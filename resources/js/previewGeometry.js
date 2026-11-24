const positive = value => Number.isFinite(value) && value > 0;
const nonempty = value => typeof value === 'string' && value.trim().length > 0;
const notes = value => Array.isArray(value) && value.length > 0 && value.every(nonempty);

const common = {
    girder: 'rectangle', left_carriage: 'rectangle', right_carriage: 'rectangle',
    left_rail_centerline: 'line', right_rail_centerline: 'line',
};
const components = {
    front: { ...common, hoist: 'rectangle', hook_stem: 'line', hook_tip: 'line', lifting_height: 'text', capacity: 'text' },
    top: { ...common, trolley: 'rectangle' },
};
const dimensions = { front: ['span', 'girder_depth'], top: ['span', 'girder_width', 'end_carriage_length'] };

function validShape(shape, type = shape?.type) {
    const g = shape?.geometry;
    if (!g || !nonempty(shape.id) || shape.type !== type) return false;
    switch (type) {
        case 'line': return [g.x1, g.y1, g.x2, g.y2].every(Number.isFinite);
        case 'rectangle': return [g.x, g.y].every(Number.isFinite) && [g.width, g.height].every(positive);
        case 'text': return [g.x, g.y].every(Number.isFinite) && positive(g.fontSize)
            && nonempty(g.text) && ['start', 'middle', 'end'].includes(g.anchor);
        default: return false;
    }
}

function uniqueIds(items) {
    return new Set(items.map(item => item?.id)).size === items.length;
}

function validView(view, key, parameters) {
    const box = view?.viewBox;
    if (!box || ![box.x, box.y].every(Number.isFinite) || ![box.width, box.height].every(positive)
        || view.preserveAspectRatio !== 'xMidYMid meet') return false;
    const shapes = view.components;
    const dims = view.dimensions;
    return Array.isArray(shapes) && uniqueIds(shapes) && shapes.every(shape => validShape(shape))
        && Object.entries(components[key]).every(([id, type]) => shapes.some(s => s.id === id && s.type === type))
        && Array.isArray(dims) && uniqueIds(dims) && dims.length === dimensions[key].length
        && dims.every(d => d && dimensions[key].includes(d.id) && d.value === parameters[d.id]
            && validShape(d.line, 'line') && validShape(d.label, 'text') && d.label.geometry.text === `${d.value} mm`
            && Array.isArray(d.extensions) && d.extensions.length === 2 && uniqueIds(d.extensions)
            && d.extensions.every(line => validShape(line, 'line')));
}

export function validPreview(data) {
    const p = data?.parameters;
    return p && nonempty(p.name) && p.crane_type === 'single_girder' && data.drawing_type === 'single_girder_eot'
        && [p.capacity, p.span, p.lifting_height, p.girder_depth, p.girder_width, p.end_carriage_length].every(positive)
        && data.units?.length === 'mm' && data.units.capacity === 't'
        && notes(data.presentation_assumptions) && notes(data.engineering_limitations)
        && Object.keys(components).every(key => validView(data.views?.[key], key, p));
}
