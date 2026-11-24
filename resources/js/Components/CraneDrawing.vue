<script setup>
import { computed } from 'vue';

const props = defineProps({ geometry: Object, view: String, pending: Boolean, error: String, outdated: Boolean });
const emit = defineEmits(['update:view']);
const views = { front: 'Front View', top: 'Top View' };
const drawing = computed(() => props.geometry?.views[props.view]);

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
                    :aria-selected="view === key" :aria-controls="'panel-' + key" :tabindex="view === key ? 0 : -1"
                    class="rounded px-3 py-1.5 text-sm font-medium" :class="{ 'bg-slate-800 text-white': view === key }"
                    @click="emit('update:view', key)" @keydown="navigate">{{ label }}</button>
            </div>
        </div>
        <p v-if="outdated" role="status" class="bg-amber-100 px-5 py-2 text-sm text-amber-950">
            Preview outdated. Generate again to apply your changes to the drawing and summary.
        </p>
        <p v-if="error" role="alert" class="bg-red-50 px-5 py-2 text-sm text-red-900">{{ error }}</p>
        <div v-for="(_, key) in views" :id="'panel-' + key" :key="key" :hidden="view !== key"
            role="tabpanel" :aria-labelledby="'tab-' + key" tabindex="0">
            <div v-if="view === key" class="grid min-h-52 place-content-center bg-slate-50 px-5 text-center" role="status">
                <p v-if="pending">Generating preview…</p>
                <template v-else-if="drawing">
                    <p class="font-semibold">{{ views[key] }} geometry ready</p>
                    <p class="mt-1 text-sm text-slate-600">{{ drawing.components.length }} schematic components ·
                        {{ drawing.dimensions.length }} dimensions. Drawing rendering is planned for Phase 6.</p>
                </template>
                <template v-else>
                    <p class="font-semibold">{{ error ? 'Preview unavailable' : 'No preview yet' }}</p>
                    <p class="mt-1 text-sm text-slate-600">Use Generate Preview to calculate geometry for this design.</p>
                </template>
            </div>
        </div>
        <p class="border-t border-slate-200 px-5 py-3 text-sm text-slate-700">Preliminary drawing only. Not approved for manufacturing or structural safety assessment.</p>
    </section>
</template>
