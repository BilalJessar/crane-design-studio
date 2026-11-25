# Project Decisions

## 1. Who is the user, and what do they need?

The users of this application are site engineers and crane design engineers. I assume that creating and updating crane drawings manually can be repetitive and time consuming, although I'm not familiar with the existing workflow of the company.

The web-app allows engineers to enter crane dimensions and generate a 2D drawing automatically. They can save, reopen and update their crane designs without creating a new drawing every time a dimension changes.

Storing designs digitally also reduces the risk of losing design and dimension information. The main purpose is to simplify the drawing generation of crane, not to replace professional engineering or CAD software.

## 2. What did you assume, and why?

### Crane Type

I selected a Single Girder EOT Crane since it's a time-limited assignment. One crane type keeps the implementation manageable and allows me to focus only on the core requirements.

### Input Parameters

- Design name, crane type, capacity, span, lifting height, girder depth, girder width and end carriage length.
- Limited inputs to generate front and top views.
- Girder depth and width are entered directly by the user due to the lack of verified engineering formulas and material specs.
- Capacity and lifting height requirements are just labels, not the part of structural calculations.
- My reasoning for these input parameters: The goal is to keep the app simple by generating the useful dimensional drawings from minimal inputs, without relying on unverified engineering calculations.

### Measurement Units

- Millimeters are used for dimensions, and metric tonnes for capacity.
- Whole-millimeter precision is assumed to be sufficient for preliminary drawings.
- Decimal values like (3.25 and 7.5 tonnes) are supported to allow fractional capacities.
- Measurement precision still needs to be confirmed by engineers.

### Validation Ranges

| Parameter | Minimum | Maximum |
|---|---:|---:|
| Capacity | 0.5 tonnes | 20 tonnes |
| Span | 3000 mm | 30000 mm |
| Lifting Height | 1000 mm | 20000 mm |
| Girder Depth | 100 mm | 2000 mm |
| Girder Width | 100 mm | 1500 mm |
| End Carriage Length | 500 mm | 6000 mm |

- These are provisional application level validation ranges.
- Their purpose is to restrict negative, invalid, and out of range inputs.
- These ranges have not been verified against any certified engineering standard.
- Passing validation does not mean an input or configuration is safe for manufacturing or lifting operations.
- My reasoning for these validation ranges: Maintain reliability using controlled input ranges, while staying flexible to update validation rules when actual engineering limits are confirmed.

## 3. Where did each formula or rule come from, and how sure are you?

### Physical Geometry

- Left rail position = 0.
- Right rail position = S.
- Midpoint = S / 2.
- Front View girder depth is taken from user input.
- Top View girder width and end carriage length are taken directly from user inputs.
- Top girder starting Y coordinate = -W/2.
- End carriage starting Y coordinate = -L/2.

- S = Span
- D = Girder Depth
- W = Girder Width
- L = End Carriage Length


### Presentation Rules

- Front View spacing = Span / 80.
- Top View spacing = max(Span, End Carriage Length, Girder Width) / 80.
- The viewBox fits the drawing and annotations into the available space.
- Preserving the aspect ratio prevents the drawing from distorting.
- The hoist, hook, and trolley are schematic representations.

### Formula Sources

| Formula or Rule | Source |
|---|---|
| Rail positions (0 and S) | Coordinate system choice and user-provided span |
| Midpoint (S / 2) | Basic mathematics |
| Girder depth, width and end carriage length | User-provided dimensions |
| Centering (-W/2 and -L/2) | Basic coordinate geometry |
| Front View spacing (S / 80) | Presentation-only layout choice |
| Top View spacing (max(S, L, W) / 80) | Presentation-only layout choice |
| ViewBox and aspect ratio | SVG positioning and scaling rules |
| Hoist, hook and trolley dimensions | Schematic representation assumptions |
| Validation ranges | Provisional application-level limits |

### Confidence and Limitations

- Basic math calculations and how entered dimensions are shown have been checked with automated tests and browser testing.
- Symbol sizes, spacing, and margins are presentation assumptions.
- The validation ranges have not been verified by crane engineers.
- Structural safety, load bearing capacity, and manufacturing suitability haven't been verified.
- The reference drawing of the assignment is illustrative.

Detailed formulas: [Geometry Documentation](docs/crane-geometry.md)

My judgment: Testing can give confidence in the basic math and dynamic drawing generation, but engineering-specific assumptions, dimensional limits, and structural relationships still need to be verified by qualified engineers.

## 4. What did you leave out on purpose?

- Multiple crane types.
- Automatic girder sizing.
- Structural strength calculations.
- Safe working load verification.
- Deflection and wheel load calculations.
- Manufacturing-grade drawings.
- Complex CAD integration.
- Authentication and user roles.
- Design revision history.

- Since the scope for the assignment was limited, I mainly focused on the core requirements.
- Verified engineering formulas weren't available, so I didn't take the risk to include incorrect calculations.
- My main priority was to build the functional app with drawings, server-side validation and to save designs, reopen and functionality to update.

My reasoning: Rather than adding more features, I prefer a smaller scope with a reliable, functional app. I only want to include features we can properly build and test, and avoid wrong or misleading calculations where we don't have the engineering knowledge.

## 5. What three questions would you ask our engineers?

1. Are our selected inputs enough to generate Single Girder EOT Crane preliminary drawing? Or do we need additional inputs? Or what should be the actual acceptable min and max ranges?

2. Should we allow the user to directly type the values for depth and width? Or should we auto calculate these values on the basis of capacity and span? If we need to calculate them automatically, which approved engineering formulas, standards and parameters will be required?

3. Are the front and top views enough for preliminary crane design, or are additional views, dimensions, hook clearances and component details required?

## 6. What would you do with another week?

- Existing requirements and assumptions should be verified with Crane engineers.
- Update provisional validation ranges to match approved specifications.
- Improve the geometry service when verified engineering formulas are available.
- Add support for Double Girder EOT Cranes.
- Add extra drawing views and more detailed annotations.
- Implement design revision history.
- Improve frontend automated testing.
- Explore CAD-compatible DXF export.

My priorities: Improve the engineering accuracy and reliability of the existing application before adding new features. Then expand the scope around verified requirements so future development isn't built on incorrect assumptions.