# Crane geometry preview

`POST /api/designs/generate` uses the same eight required fields and limits as design creation. The controller passes the validated fields to `CraneGeometryService::generate(array $parameters)` and wraps its return value in `data`. The service has no HTTP, database, clock or random dependencies. It normalizes numeric inputs once, so a whole-millimeter string such as `"15000"` comes back as a number. Identical validated inputs give identical drawings.

## Response contract

| Field under `data` | Contents |
| --- | --- |
| `parameters` | Name and crane type as strings, capacity as a JSON number, and all five lengths as integer millimeters |
| `units` | `{"length":"mm","capacity":"t"}` where t means metric tonnes |
| `drawing_type` | `single_girder_eot` |
| `views.front`, `views.top` | Each contains `components` in paint order, `dimensions` in annotation order, `viewBox` with numeric `x,y,width,height`, and `preserveAspectRatio` |
| `presentation_assumptions`, `engineering_limitations` | Arrays of human-readable strings to accompany the drawing |

Every component is an object with a stable `id` within the view, a `type` and a `geometry` object. Rectangles carry `x,y,width,height`. Lines carry `x1,y1,x2,y2`. Text carries `x,y,text,fontSize,anchor`, where y is the baseline and anchor is the SVG text-anchor value. Geometry is numeric except for text content and anchor. A renderer can dispatch by type and use the component ID for styling, for example to dash the rail centerlines.

Every dimension contains `id` (the input field), `value` (millimeters), a pair of extension lines in `extensions`, and complete `line` and `label` shapes. Draw these after the components. The front dimensions are span and girder depth. The top dimensions are span, carriage length and girder width. Required lifting height and required capacity are standalone front text components.

Success returns HTTP 200. Invalid input returns 422 with Laravel's `message` and field-keyed `errors`. Unexpected exceptions use the existing JSON error handler, with HTTP 500 and `{"message":"Server Error"}` when debug is disabled.

## Calculation reference

One coordinate unit represents one millimeter on both axes. Positive x goes right and positive y goes down. Let S be span, D girder depth, W girder width and L end carriage length. These four lengths come directly from the validated request. A rectangle below is written as (left, top, width, height).

| Element | Formula | Origin or justification |
| --- | --- | --- |
| Rail positions | Left x = 0, right x = S, midpoint M = S/2 | Origin choice and center-to-center definition of span |
| Front girder | (0, 0, S, D) | Front origin at left rail and upper girder face. Draw the supplied depth over the supplied span |
| Top girder | (0, −W/2, S, W) | Center the supplied width about y = 0 |
| Layout unit u | Front S/80. Top max(S,L,W)/80 | Presentation choice. Bases symbols and annotation spacing on the extent of the view |
| Top carriages | (x−u/2, −L/2, u, L) at each rail x | Supplied length runs along the rail. Symmetric placement centers the rectangle on the rail. Thickness u is symbolic |
| Top envelope and rails | E = max(W,L)/2. Each rail runs from −E−u to E+u | Half-extent of the larger centered rectangle, plus a visible centerline extension |
| Front rails | y from −u to D+u | Extend center marks beyond girder faces |
| Hoist and hook | Hoist (M−u,D,2u,u). Stem (M,D+u) to (M,D+3u). Tip continues to (M+u,D+2u) | Centered presentation symbols. Their geometry conveys neither hook travel nor clearance |
| Front carriage symbols | Centered on each rail, width and height u, immediately above the girder | Symbolic envelope only |
| Top trolley | Centered at (M,0), width and height 2u | Presentation symbol |
| Requirements | Centered baselines at (M,D+6.5u) for lift and (M,D+9u) for capacity | Text records the requirements below the hook |
| Span line | x from 0 to S. Front y = −2u. Top y = −E−2u | Endpoints align with rail centers. Offset places the annotation above components |
| Vertical dimension lines | Front depth at x = S+2u, y from 0 to D. Top width at x = S+2u, y from −W/2 to W/2. Top length at x = −2u, y from −L/2 to L/2 | Endpoints match the measured faces. Exterior lanes separate labels even when W = L |
| Labels | Start at endpoint midpoint ((x1+x2)/2,(y1+y2)/2). Horizontal label moves up u and centers. Vertical label moves outward u, anchored end on the left and start on the right. Font size = 1.6u | Arithmetic midpoint and presentation spacing. Label text uses the original integer followed by mm |
| Vertical bounds | Front T = −2u, B = D+9u. Top T = −E−2u, B = E+u | T is the span line. B is the lowest text baseline or rail endpoint respectively |
| ViewBox | (−12u, T−3u, S+24u, B−T+6u) | Add side gutters for values and top/bottom gutters for labels and strokes. Gutters are layout choices |
| Aspect ratio | `xMidYMid meet` corresponds to uniform scale min(viewport width / box width, viewport height / box height) | Fit and center the entire drawing. Positive validated lengths give u > 0 and positive box dimensions |

The application assumes a schematic single girder between rail centers. Overhangs, connection details, hoist location, symbol sizes, offsets, rail extents, typography and margins have no manufacturing significance. Capacity is a user-entered rating and lifting height is a requirement. Neither determines sizing, overall height or hook clearance. The generator performs no checks of strength, deflection, wheel loads, motor power, safe working load or manufacturing tolerances. The response identifies the drawing as preliminary and unapproved for manufacturing or structural safety assessment. The formulas above derive from the supplied dimensions, elementary coordinate geometry and explicit layout choices.

## Dimension extensions and SVG rendering

For a horizontal span line, each extension keeps its endpoint's x and joins the reference y to the dimension y. The front reference is −u and the top reference is −E−u, the upper rail endpoints. For a vertical dimension, each extension keeps its endpoint's y and joins the reference x to the dimension x. The depth and width references are x = S at the girder end. The carriage-length reference is x = −u/2 at the outside of the left carriage symbol. These are drafting choices in `CraneGeometryService::dimension`. They leave the reported physical lengths intact.

The following rules come from `CraneDrawing.vue`, `DrawingShape.vue` and `downloadDrawing.js`. They control presentation. Coordinates and font sizes inside the viewBox use drawing units. Percentages, rem and exported pixel dimensions describe the display surface.

| Rule | Relationship and rationale |
| --- | --- |
| Shape order and style | Paint the two rail centerlines first, then the service's components in order. Draw each dimension's two extensions, arrowed line and label above them. Rectangles, lines and text use their supplied coordinates directly. Gray identifies the girder, amber the mechanical symbols and teal the dimensions. Non-scaling strokes keep outlines legible during zoom. Rail strokes use width 1 and dash pattern 4 on, 2 off. Hook lines use 1.5, extensions 0.8 and dimension lines 1. |
| Arrowheads | Let F = 1.6u, the first dimension label's font size. Each marker viewport is 0.65F wide and 0.5F high in drawing units. An 8 × 6 marker box contains the triangle (0,0), (8,3), (0,6). The tip (8,3) sits on the endpoint. Automatic orientation reverses the start arrow. |
| Zoom and clipping | Zoom z starts at 1, changes by 0.25 and ranges from 0.5 to 3. Display width is 100z percent of the container and maximum height is 21z rem. The scroll area has a 24 rem maximum height. Reset or a changed drawing restores z = 1 and the scroll origin. |
| Export footer | For source box (x,y,w,h), set font f = w/78. Starting at y+h, advance 1.6f per baseline and place each line at x+f. Lines contain the view and units, legend sentences, capacity/lift caveat and disclaimer. With n lines, the new height is H = h + 1.6fn + f. The extra f leaves a bottom margin. |
| Export viewport | Keep (x,y,w) and substitute H for h. Remove screen zoom styling, copy computed shape styles, set width to 1200 px and height to 1200H/w px. This preserves the expanded box's aspect ratio. Export has no calibrated print scale. |

The assignment reference drawing is illustrative. Its appearance supplies no certified dimensions or design criteria. The validation ranges in the README govern acceptable application inputs. They supply no engineering relationship between span, lift, capacity and girder size.

## Reproduce the example

From the project root with the Compose service running, use PowerShell:

```powershell
docker compose ps
docker compose exec app php artisan route:list --path=api
docker compose exec app php artisan test
$url = 'http://127.0.0.1:8000/api/designs'
$before = (Invoke-WebRequest -UseBasicParsing $url).Content
$body = '{"name":"Crane 001","crane_type":"single_girder","capacity":5,"span":15000,"lifting_height":6000,"girder_depth":500,"girder_width":300,"end_carriage_length":2500}'
$response = Invoke-WebRequest -UseBasicParsing "$url/generate" -Method Post -ContentType application/json -Body $body
$response.StatusCode
($response.Content | ConvertFrom-Json) | ConvertTo-Json -Depth 12
$before -ceq (Invoke-WebRequest -UseBasicParsing $url).Content
```

Expect 200 and a final comparison of True. For this request u = 187.5 in both views and E = 1250 in plan. The front box is (−2250, −937.5, 19500, 3687.5). The top box is (−2250, −2187.5, 19500, 4187.5). The top girder starts at y = −150 and the carriages start at y = −1250. The following complete dimension object appears in `data.views.front.dimensions`:

```json
{
  "id": "girder_depth",
  "value": 500,
  "line": {
    "id": "girder_depth_line",
    "type": "line",
    "geometry": {"x1": 15375, "y1": 0, "x2": 15375, "y2": 500}
  },
  "extensions": [
    {"id": "girder_depth_start", "type": "line", "geometry": {"x1": 15000, "y1": 0, "x2": 15375, "y2": 0}},
    {"id": "girder_depth_end", "type": "line", "geometry": {"x1": 15000, "y1": 500, "x2": 15375, "y2": 500}}
  ],
  "label": {
    "id": "girder_depth_label",
    "type": "text",
    "geometry": {"x": 15562.5, "y": 250, "text": "500 mm", "fontSize": 300, "anchor": "start"}
  }
}
```
