<script setup>
import { computed, ref, useId, watch } from 'vue';
import { downloadDrawing } from '../downloadDrawing';
import DrawingShape from './DrawingShape.vue';

const props = defineProps({ geometry: Object, view: String, pending: Boolean, error: String, outdated: Boolean });
const emit = defineEmits(['update:view']);
const views = { front: 'Front View', top: 'Top View' };
const drawing = computed(() => props.geometry?.views?.[props.view]);
const marker = useId() + '-arrow';
const zoom = ref(1);
const viewport = ref();
const sheet = ref();
const legend = ref();
const disclaimer = 'Preliminary drawing only. Not approved for manufacturing or structural safety assessment.';
const box = computed(() => {
    const { x, y, width, height } = drawing.value.viewBox;
    return [x, y, width, height].join(' ');
});

function resetZoom() {
    zoom.value = 1;
    viewport.value?.scrollTo(0, 0);
}
watch(drawing, resetZoom);


function navigate(event) {
    const next = { ArrowLeft: props.view === 'front' ? 'top' : 'front',
        ArrowRight: props.view === 'front' ? 'top' : 'front', Home: 'front', End: 'top' }[event.key];
    if (!next) return;
    event.preventDefault();
    emit('update:view', next);
    document.getElementById('tab-' + next).focus();
}
</script>

<template>
    <section class="overflow-hidden rounded-lg border border-slate-300 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <h2 class="text-lg font-semibold">Drawing preview</h2>
            <div role="tablist" aria-label="Drawing view" class="flex gap-1 rounded-md bg-slate-100 p-1">
                <button v-for="(label, key) in views" :id="'tab-' + key" :key="key" role="tab"
                    :aria-selected="view === key" aria-controls="drawing-panel" :tabindex="view === key ? 0 : -1"
                    class="rounded px-3 py-1.5 text-sm font-medium" :class="{ 'bg-slate-800 text-white': view === key }"
                    @click="emit('update:view', key)" @keydown="navigate">{{ label }}</button>
            </div>
        </div>
        <p v-if="outdated" role="status" class="bg-amber-100 px-5 py-2 text-sm text-amber-950">
            Preview outdated. Generate again to apply your changes to the drawing and summary.
        </p>
        <p v-if="error" role="alert" class="bg-red-50 px-5 py-2 text-sm text-red-900">{{ error }}</p>
        <p v-if="pending" role="status" class="px-5 py-2 text-sm">Generating preview...</p>
        <div id="drawing-panel" role="tabpanel" :aria-labelledby="'tab-' + view" tabindex="0">
            <template v-if="drawing">
                <div class="flex flex-wrap items-center gap-2 px-5 py-2 text-sm">
                    <span class="mr-auto font-medium">Zoom <output>{{ zoom * 100 }}%</output></span>
                    <button class="zoom" :disabled="zoom <= 0.5" @click="zoom -= 0.25">Zoom Out</button>
                    <button class="zoom" :disabled="zoom >= 3" @click="zoom += 0.25">Zoom In</button>
                    <button class="zoom" @click="resetZoom">Reset Zoom</button>
                    <button class="zoom" @click="downloadDrawing(sheet, geometry.parameters.name, views[view], legend.innerText, disclaimer)">Download SVG</button>
                </div>
                <div ref="viewport" tabindex="0" :aria-label="views[view] + ' scroll area'"
                    class="max-h-96 overflow-auto border-y border-slate-200 bg-slate-50 py-4">
                    <svg ref="sheet" xmlns="http://www.w3.org/2000/svg" :viewBox="box" :preserveAspectRatio="drawing.preserveAspectRatio"
                        class="mx-auto block max-w-none" :style="{ width: zoom * 100 + '%', maxHeight: zoom * 21 + 'rem' }"
                        role="img" :aria-label="geometry.parameters.name + ' - ' + views[view]">
                        <title>{{ views[view] }} - {{ geometry.parameters.name }}</title>
                        <defs>
                            <marker :id="marker" viewBox="0 0 8 6" refX="0" refY="3" orient="auto-start-reverse"
                                markerUnits="userSpaceOnUse" :markerWidth="drawing.dimensions[0].label.geometry.fontSize * 0.65"
                                :markerHeight="drawing.dimensions[0].label.geometry.fontSize * 0.5">
                                <path d="M8 0 L0 3 L8 6 Z" fill="#0f766e" />
                            </marker>
                        </defs>
                        <DrawingShape v-for="shape in drawing.components" :key="shape.id" :shape="shape"
                            :data-part="shape.id" class="part" />
                        <g v-for="dim in drawing.dimensions" :key="dim.id" :data-dimension="dim.id">
                            <title>{{ dim.id.replaceAll('_', ' ') }} - {{ dim.label.geometry.text }}</title>
                            <DrawingShape v-for="line in dim.extensions" :key="line.id" :shape="line" class="extension" />
                            <DrawingShape :shape="dim.line" class="dimension" :marker-start="'url(#' + marker + ')'"
                                :marker-end="'url(#' + marker + ')'" />
                            <DrawingShape :shape="dim.label" class="measurement" />
                        </g>
                    </svg>
                </div>
                <p ref="legend" class="px-5 py-3 text-sm text-slate-700">
                    <strong>Dashed lines</strong> mark runway centers.
                    <template v-if="view === 'front'">Amber symbols show the end carriages, hoist and hook schematically.</template>
                    <template v-else>Amber marks the schematic trolley and end carriages. Only the carriage length is dimensioned.</template>
                </p>
            </template>
            <div v-else-if="!pending" class="grid min-h-52 place-content-center bg-slate-50 px-5 text-center" role="status">
                <p class="font-semibold">{{ error ? 'Preview unavailable' : 'No preview yet' }}</p>
                <p class="mt-1 text-sm text-slate-600">Use Generate Preview to calculate geometry for this design.</p>
            </div>
        </div>
        <p class="border-t border-slate-200 px-5 py-3 text-sm text-slate-700">{{ disclaimer }}</p>
    </section>
</template>

<style scoped>
@reference "../../css/app.css";
.zoom { @apply rounded border border-slate-300 px-2 py-1 font-medium disabled:opacity-40; }
.part, .extension, .dimension { vector-effect: non-scaling-stroke; }
rect.part { fill: #fef3c7; stroke: #92400e; }
rect[data-part="girder"] { fill: #cbd5e1; stroke: #334155; }
line.part { stroke: #92400e; stroke-width: 1.5; }
line[data-part$="centerline"] { stroke: #475569; stroke-dasharray: 4 2; stroke-width: 1; }
text.part { fill: #334155; }
.extension { stroke: #94a3b8; stroke-width: 0.8; }
.dimension { stroke: #0f766e; stroke-width: 1; }
.measurement { fill: #115e59; font-weight: 600; }
</style>
