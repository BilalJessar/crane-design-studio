<?php

namespace App\Services;

class CraneGeometryService
{
    /** Generate a preliminary drawing from validated single-girder design parameters. */
    public function generate(array $parameters): array
    {
        foreach (['span', 'girder_depth', 'girder_width', 'lifting_height', 'end_carriage_length'] as $field) {
            $parameters[$field] = (int) $parameters[$field];
        }
        $parameters['capacity'] = (float) $parameters['capacity'];

        return [
            'parameters' => $parameters,
            'units' => ['length' => 'mm', 'capacity' => 't'],
            'drawing_type' => 'single_girder_eot',
            'views' => ['front' => $this->front($parameters), 'top' => $this->top($parameters)],
            'presentation_assumptions' => [
                'Model coordinates increase rightward and downward. Use uniform scaling in each view.',
                'Rail line extents, carriage thickness, centered hoist and hook symbols, text sizes and annotation offsets are presentation only.',
                'The girder is drawn between rail centers. Overhangs and connection details are unspecified.',
            ],
            'engineering_limitations' => [
                'Preliminary drawing. Not approved for manufacturing or structural safety assessment.',
                'Capacity and required lifting height are user supplied. Load rating, strength, deflection, wheel loads, motor power, clearance, overall height and tolerances are unverified.',
            ],
        ];
    }

    private function front(array $p): array
    {
        $span = $p['span'];
        $depth = $p['girder_depth'];
        $u = $span / 80;
        $mid = $span / 2;
        $bottom = $depth + 5 * $u;
        $components = [
            ...$this->rails($span, -$u, $depth + $u),
            $this->rectangle('girder', 0, 0, $span, $depth),
            $this->rectangle('hoist', $mid - $u, $depth, 2 * $u, $u),
            $this->line('hook_stem', $mid, $depth + $u, $mid, $depth + 3 * $u),
            $this->line('hook_tip', $mid, $depth + 3 * $u, $mid + $u, $depth + 2 * $u),
            $this->label('lifting_height', $mid, $bottom,
                "Required lifting height: {$p['lifting_height']} mm", $u, 'middle'),
        ];

        return $this->view($span, -2 * $u, $bottom, $u, $components, [
            $this->dimension('span', $span, 0, -2 * $u, $span, -2 * $u, $u),
            $this->dimension('girder_depth', $depth, $span + 2 * $u, 0, $span + 2 * $u, $depth, $u),
        ]);
    }

    private function top(array $p): array
    {
        $span = $p['span'];
        $length = $p['end_carriage_length'];
        $width = $p['girder_width'];
        $u = max($span, $length, $width) / 80;
        $edge = max($length, $width) / 2;
        $spanY = -$edge - 2 * $u;
        $components = [
            ...$this->rails($span, -$edge - $u, $edge + $u),
            $this->rectangle('girder', 0, -$width / 2, $span, $width),
            $this->rectangle('left_carriage', -$u / 2, -$length / 2, $u, $length),
            $this->rectangle('right_carriage', $span - $u / 2, -$length / 2, $u, $length),
        ];

        return $this->view($span, $spanY, $edge + $u, $u, $components, [
            $this->dimension('span', $span, 0, $spanY, $span, $spanY, $u),
            $this->dimension('end_carriage_length', $length, -2 * $u, -$length / 2, -2 * $u, $length / 2, $u),
            $this->dimension('girder_width', $width, $span + 2 * $u, -$width / 2, $span + 2 * $u, $width / 2, $u),
        ]);
    }

    private function view($span, $top, $bottom, $u, array $components, array $dimensions): array
    {
        return [
            'viewBox' => ['x' => -12 * $u, 'y' => $top - 3 * $u,
                'width' => $span + 24 * $u, 'height' => $bottom - $top + 6 * $u],
            'preserveAspectRatio' => 'xMidYMid meet',
            'components' => $components,
            'dimensions' => $dimensions,
        ];
    }

    private function rails($span, $top, $bottom): array
    {
        return [
            $this->line('left_rail_centerline', 0, $top, 0, $bottom),
            $this->line('right_rail_centerline', $span, $top, $span, $bottom),
        ];
    }

    private function dimension(string $id, int $value, $x1, $y1, $x2, $y2, $u): array
    {
        $x = ($x1 + $x2) / 2;
        $y = ($y1 + $y2) / 2;
        $anchor = 'middle';
        if ($x1 == $x2) {
            $anchor = $x1 < 0 ? 'end' : 'start';
            $x += $x1 < 0 ? -$u : $u;
        } else {
            $y -= $u;
        }

        return [
            'id' => $id, 'value' => $value,
            'line' => $this->line($id.'_line', $x1, $y1, $x2, $y2),
            'label' => $this->label($id.'_label', $x, $y, "$value mm", $u, $anchor),
        ];
    }

    private function line(string $id, $x1, $y1, $x2, $y2): array
    {
        return ['id' => $id, 'type' => 'line', 'geometry' => compact('x1', 'y1', 'x2', 'y2')];
    }

    private function rectangle(string $id, $x, $y, $width, $height): array
    {
        return ['id' => $id, 'type' => 'rectangle', 'geometry' => compact('x', 'y', 'width', 'height')];
    }

    private function label(string $id, $x, $y, string $text, $fontSize, string $anchor): array
    {
        return ['id' => $id, 'type' => 'text', 'geometry' => compact('x', 'y', 'text', 'fontSize', 'anchor')];
    }
}
