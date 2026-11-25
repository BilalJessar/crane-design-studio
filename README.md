# Crane Design Studio

A parametric overhead crane drawing generator for the Modern Crane Co. assignment. An engineer can enter a single-girder EOT crane’s dimensions, compare labelled front and top views, and reopen a saved design when a dimension changes. This reduces the need to redraw a preliminary layout for each revision.

## Install and run with Docker

Install Docker Desktop with Linux containers on Windows or macOS, or Docker Engine and the Compose v2 plugin on Linux. Start Docker and confirm that `docker compose version` succeeds. Allow internet access for the first build and dependency downloads, write access to the checkout, and free local ports 8000 and 5173. Docker supplies PHP, Composer, Node and npm.

```sh
git clone https://github.com/BilalJessar/crane-design-studio.git
cd crane-design-studio
docker compose up --build
```

Wait for Artisan to report that the server is running. Open the [design editor](http://localhost:8000) or the [JSON health check](http://localhost:8000/api/health). Run the remaining commands in a second terminal in the project directory. Stop with `docker compose down` and restart with `docker compose up`.

### Environment, database and sample data

On startup, `docker/start.sh` runs Composer install and npm ci against the committed lockfiles. It copies `.env.example` to `.env` if missing, generates a Laravel application key when needed, creates `database/database.sqlite` if absent, runs pending migrations, clears the Vite hot-file marker, builds assets and starts the server. The supplied environment uses SQLite, database-backed sessions and cache, and disabled debug output. No external service credentials are required.

The SQLite file and environment live in the host checkout. Composer and npm dependencies live in named Docker volumes. A fresh database starts with an empty design list. To add the three illustrative designs, run the following command. The seeder preserves existing entries with the same sample names and also creates the default test user if missing.

```sh
docker compose exec app php artisan db:seed
docker compose exec app php artisan migrate:status
```

## Use the editor

1. Open **New Design**, name the design and enter the required capacity and dimensions.
2. Select **Generate Preview**. **Front View** shows span and depth with the capacity and lifting-height requirements. **Top View** shows span, girder width and carriage length. Zoom, reset the zoom or download the current view as an SVG with its legend and disclaimer.
3. Select **Save Design**. The permanent edit URL identifies the saved record. **Saved Designs** lists records by most recent update. Select **Edit** to restore the inputs and regenerate the drawing.
4. Change a dimension and generate again to inspect the revised drawing and summary. **Save Changes** updates the record. Refresh the edit page to reload its saved values. On the list, **Delete** opens a confirmation with options to cancel or remove that design.

## Stack and architecture

| Layer | Implementation and purpose |
| --- | --- |
| HTTP and validation | PHP 8.3 and Laravel 12 handle routing, shared request validation, JSON responses and migrations. Web routes supply Inertia page props. API routes call `CraneDesignController`. |
| Storage | Eloquent persists the eight input fields in SQLite, which keeps local setup to one service. The resource returns those fields, the ID and the update timestamp. |
| Geometry | `CraneGeometryService` takes validated parameters and returns deterministic primitives, dimension labels and view bounds for both views. Preview generation performs no design write. |
| Editor | Inertia.js 2 and Vue 3 provide the editor and list in one Laravel project. Vue renders the SVG, tracks unsaved edits and requests previews and CRUD operations using Axios. SweetAlert2 handles confirmations and notifications. |
| Assets and runtime | Tailwind CSS 4 styles the interface. Vite bundles JS and CSS. Docker Compose runs the application and its dependency tools with a consistent PHP and Node environment. |

## JSON API

| Method and path | Success response |
| --- | --- |
| GET /api/health | 200 with `{"status":"ok"}` |
| POST /api/designs/generate | 200 with drawing parameters, units, both views and limitations under `data` |
| GET /api/designs | 200 with an array under `data`, newest update first, then highest ID |
| POST /api/designs | 201 with the created design under `data` |
| GET /api/designs/{id} | 200 with one design under `data` |
| PUT /api/designs/{id} | 200 with the updated design under `data`. Supply all eight fields. |
| DELETE /api/designs/{id} | 204 with an empty body |

Example preview request in a POSIX shell (including Git Bash on Windows):

```sh
curl -X POST http://localhost:8000/api/designs/generate \
  -H 'Content-Type: application/json' \
  -d '{"name":"Workshop A","crane_type":"single_girder","capacity":3.25,"span":12000,"lifting_height":5000,"girder_depth":450,"girder_width":250,"end_carriage_length":2200}'
```

The same JSON body works for creation and update. Invalid input returns 422 with a message and field-keyed `errors`. A missing record returns 404.

### Server-side validation

All fields are required on preview, creation and update. Numeric bounds are inclusive. These are provisional application limits from `config/crane_design.php`.

| Field | Accepted value |
| --- | --- |
| name | Nonblank string, up to 150 characters |
| crane_type | `single_girder` |
| capacity | 0.5–20 metric tonnes, up to two decimal places |
| span | 3,000–30,000 whole mm |
| lifting_height | 1,000–20,000 whole mm |
| girder_depth | 100–2,000 whole mm |
| girder_width | 100–1,500 whole mm |
| end_carriage_length | 500–6,000 whole mm |

## Geometry and engineering limitations

The service places rail centers at zero and the supplied span, then projects the entered girder and carriage dimensions onto two uniformly scaled views. The hook, trolley, annotation offsets and other symbolic sizes follow presentation rules. The calculation reference covers inputs, units, formulas, assumptions and SVG export. [Geometry and rendering reference](docs/crane-geometry.md)

**Preliminary drawing only.** The assignment illustration provides visual context. The application does not verify structural strength, safe working loads, deflection, clearance or manufacturing suitability. Capacity and lifting height are user requirements. The design supports one crane type, integer millimeter dimensions and local use without authentication. Saved designs store parameters, so later changes to the renderer can affect their appearance.

## Automated checks

With Compose running, execute:

```sh
docker compose ps
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint --test
docker compose exec app npm run build
git diff --check
```

PHPUnit uses in-memory SQLite. The suite covers validation boundaries, CRUD, sample data, deterministic geometry, preview responses and editor routes. The production build writes the Vite manifest and assets under `public/build`.

## Actual development hours

The assignment was completed over approximately one day, with around 15 hours of active work, including requirement analysis, development, testing, debugging and documentation.

I took additional time beyond the recommended four hours to review the implementation, verify the application behavior and resolve issues identified during testing.
