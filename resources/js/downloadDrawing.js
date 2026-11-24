// Capture the rendered styles so the file can be opened without the application's CSS.
export function downloadDrawing(source, name, view, legend, disclaimer) {
    const ns = 'http://www.w3.org/2000/svg';
    const svg = source.cloneNode(true);
    const box = source.viewBox.baseVal;
    const font = box.width / 78;
    const styles = ['fill', 'stroke', 'stroke-width', 'stroke-dasharray', 'vector-effect',
        'font-family', 'font-weight'];
    const originals = source.querySelectorAll('rect, line, text, path');
    svg.querySelectorAll('rect, line, text, path').forEach((node, index) => {
        const computed = getComputedStyle(originals[index]);
        for (const property of styles) node.setAttribute(property, computed.getPropertyValue(property));
    });

    const footer = [view + ' — Dimensions in millimeters', ...legend.split(/(?<=\.)\s+/),
        'Capacity and lifting height are user requirements. Safe working load is unverified.', disclaimer];
    let baseline = box.y + box.height;
    for (const line of footer) {
        const text = document.createElementNS(ns, 'text');
        baseline += font * 1.6;
        text.setAttribute('x', box.x + font);
        text.setAttribute('y', baseline);
        text.setAttribute('font-family', 'sans-serif');
        text.setAttribute('font-size', font);
        text.textContent = line;
        svg.append(text);
    }
    const height = baseline + font - box.y;
    svg.setAttribute('viewBox', [box.x, box.y, box.width, height].join(' '));
    svg.removeAttribute('style');
    svg.setAttribute('width', 1200);
    svg.setAttribute('height', 1200 * height / box.width);
    const url = URL.createObjectURL(new Blob([new XMLSerializer().serializeToString(svg)], { type: 'image/svg+xml' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = (name.replace(/[^\p{L}\p{N}_-]+/gu, '-').replace(/^-+|-+$/g, '') || 'crane') + '-' + view.split(' ')[0].toLowerCase() + '.svg';
    link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
