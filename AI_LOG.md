# AI Usage Log

## 1. AI Tools Used

### ChatGPT

Used for discussing technical approaches, understanding crane geometry concepts, evaluating architectural alternatives, and refining implementation prompts.

### OpenAI Codex

Used as an AI coding assistant throughout development, primarily for:

- Code generation and implementation assistance.
- Backend validation and geometry logic.
- Dynamic SVG rendering.
- Debugging, automated testing, and code improvements.

### My Role in Development

I was responsible for understanding the requirements, selecting the technology stack, defining the project scope, reviewing technical approaches, and providing implementation instructions.

I also performed manual testing, identified issues in the generated implementation, requested corrections, and reviewed the application against the assignment requirements.

AI assistance was used to accelerate development, while technical decisions and application behavior were evaluated throughout the process.

## 2. Key Prompts

The following are selected excerpts from four implementation prompts used during development.

### Prompt 1 — REST APIs and Server-Side Validation

**Tool:** OpenAI Codex

**Purpose:** To implement reliable REST APIs and ensure that crane design parameters are validated before processing or storing them.

**Prompt excerpts:**

> Use Laravel Form Request classes for validation. Share validation rules between create and update operations where practical.

> Use validated input instead of unrestricted request data.

> Do not claim that a design passing validation is structurally safe or suitable for manufacturing.

**Outcome:**

- REST API endpoints for creating, listing, retrieving, updating and deleting crane designs.
- Shared server-side validation.
- Centralized input validation limits.
- Consistent JSON responses and HTTP status codes.
- Automated API feature tests.

**Verification:**

The final audit confirmed that required fields, numeric types, decimal precision and input boundaries were tested. An invalid update request returned HTTP 422 without modifying the previously saved database values.

**Related commit:** `4fe60b2`

### Prompt 2 — Crane Geometry Service

**Tool:** OpenAI Codex

**Purpose:** To convert validated crane dimensions into structured geometry for generating preliminary 2D drawings without introducing unsupported engineering calculations.

**Prompt excerpts:**

> Use the input dimensions directly wherever possible.

> Prefer returning geometry in model-space coordinates rather than hardcoding pixel positions.

> Do not invent an overall crane height or infer exact hook clearance from lifting_height alone.

> Document each implemented mathematical rule and its origin.

**Outcome:**

- Dedicated CraneGeometryService.
- Front View and Top View geometry.
- Component positioning using mathematical coordinate relationships.
- Dimension annotations and dynamic drawing boundaries.
- Geometry preview API without database persistence.
- Clearly documented engineering assumptions and limitations.

**Verification:**

The final audit confirmed that the drawing dimensions corresponded to the supplied input values. Mathematical formulas, presentation choices and engineering limitations are documented in `docs/crane-geometry.md`.

**Related commit:** `af56dbe`

### Prompt 3 — Dynamic SVG Drawing Generation

**Tool:** OpenAI Codex

**Purpose:** To render backend-generated geometry as dynamic SVG drawings using Vue.

**Prompt excerpts:**

> Extend the existing CraneDrawing.vue component to render actual SVG drawings using geometry returned by the Laravel backend.

> The frontend must not duplicate engineering geometry calculations already performed by Laravel.

> Dimension labels must represent original physical measurements in millimeters, not scaled screen coordinates.

> Do not hardcode crane dimensions or component positions.

**Outcome:**

- Dynamic Front View and Top View rendering.
- Dimension lines, arrowheads and measurement labels.
- Responsive SVG drawings.
- Aspect ratio preservation.
- Zoom controls and SVG export.
- Integration with the geometry preview API.

**Verification:**

During manual testing, I changed the crane dimensions to verify that the drawing responded to different inputs. This helped me identify an issue with the initial SVG implementation, which is described in the AI mistake and correction section below.

**Related commit:** `b5eed0c`

### Prompt 4 — Code Review, Testing and Bug Fixing

**Tool:** OpenAI Codex

**Purpose:** To review the existing implementation, identify potential issues and improve application reliability before submission.

**Prompt excerpts:**

> Fix actual issues identified during the review.

> Prioritize mandatory assignment requirements over optional improvements.

> If any existing formula or drawing representation is misleading, correct it and explain the change.

> Do not claim that a command passed unless it was actually executed successfully.

**Outcome:**

- Backend and frontend code review.
- Improved validation and error handling.
- Geometry and SVG rendering verification.
- Improved button interactions and confirmation dialogs.
- Database persistence verification.
- Additional automated testing and Docker startup improvements.

**Verification:**

The subsequent final project audit reported successful automated tests, frontend compilation, browser workflow testing and installation from a clean repository clone.

**Related commit:** `ebc1a94`

## 3. AI Mistake and Correction

### Issue: Hardcoded Dimensions in the SVG Preview

During the dynamic SVG implementation, Codex reported that the drawing functionality had been implemented and verified.

However, when I manually tested the application with different crane dimensions, I noticed that the SVG preview remained unchanged instead of responding to the updated inputs.

### How I Identified the Issue

I entered different dimensional values and generated the preview again. I expected the drawing to reflect the updated measurements, but the crane representation remained the same.

This revealed that the initial preview was relying on hardcoded dimensions rather than correctly reflecting the input-dependent geometry.

### Correction

I reported the issue to Codex and requested that the SVG rendering use the actual geometry associated with the user-provided dimensions instead of fixed values.

### Verification After Correction

The final application audit confirmed that the Front View correctly represented the span and girder depth, while the Top View correctly represented the span, girder width and end carriage length.

The audit also verified that changing a saved design's span from 14,200 mm to 16,000 mm resulted in the updated measurement appearing in both the form and regenerated drawing after refreshing the application.

### Lesson Learned

AI-generated implementation reports should not be treated as a substitute for manual verification.

Testing different input combinations and comparing the actual application behavior against the expected behavior is particularly important when working with dynamic drawings and mathematical calculations.

## 4. Final Verification Results

The final project audit reported the following results:

| Verification | Result |
|---|---|
| Automated Laravel tests | 53 passed |
| Failed tests | 0 |
| Skipped tests | 0 |
| Test assertions | 3,974 |
| Laravel Pint | Passed across 42 PHP files |
| Frontend production build | Successful |
| Fresh Docker installation | Successful |
| REST API operations | Verified |
| Server-side validation | Verified |
| Dynamic SVG drawings | Verified |
| Save, reopen and edit | Verified |
| Delete confirmation | Verified |

## 5. AI-Assisted Development Approach

I followed a phase-based development approach to keep the implementation manageable and allow individual features to be reviewed and tested.

AI assistance was used for implementation, debugging and evaluating technical approaches. Manual testing helped identify issues that were not apparent from the initial AI verification reports.

The application intentionally avoids unsupported structural engineering calculations. User-provided physical dimensions, mathematical coordinate calculations and presentation-only drawing choices are treated separately.

The focus throughout development was on meeting the assignment requirements, maintaining understandable code and being transparent about engineering limitations.