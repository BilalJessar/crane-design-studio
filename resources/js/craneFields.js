export const dimensions = {
    span: 'Crane span',
    lifting_height: 'Required lifting height',
    girder_depth: 'Girder depth',
    girder_width: 'Girder width',
    end_carriage_length: 'End carriage length',
};

export function craneTypeLabel(type) {
    return type === 'single_girder' ? 'Single Girder EOT Crane' : type;
}
