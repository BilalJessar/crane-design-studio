<script setup>
import { dimensions, craneTypeLabel } from '../craneFields';

defineProps({ geometry: Object });
</script>

<template>
    <section class="rounded-lg border border-slate-300 bg-white p-5">
        <h2 class="text-lg font-semibold">Technical summary</h2>
        <p v-if="!geometry" class="mt-2 text-sm text-slate-600">Generate a preview to review the submitted requirements.</p>
        <template v-else>
            <h3 class="mt-4 font-semibold">Submitted requirements</h3>
            <dl class="mt-2 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt>Design name</dt><dd>{{ geometry.parameters.name }}</dd></div>
                <div><dt>Crane type</dt><dd>{{ craneTypeLabel(geometry.parameters.crane_type) }}</dd></div>
                <div><dt>Required capacity</dt><dd>{{ geometry.parameters.capacity }} {{ geometry.units.capacity }} (metric tonnes)</dd></div>
                <div v-for="(label, key) in dimensions" :key="key">
                    <dt>{{ label }}</dt><dd>{{ geometry.parameters[key] }} {{ geometry.units.length }}</dd>
                </div>
            </dl>
            <div v-for="(items, heading) in { 'Presentation assumptions': geometry.presentation_assumptions,
                'Engineering limitations': geometry.engineering_limitations }" :key="heading" class="mt-4 border-t border-slate-200 pt-3">
                <h3 class="font-semibold">{{ heading }}</h3>
                <ul class="mt-1 list-disc space-y-1 pl-5 text-sm"><li v-for="item in items" :key="item">{{ item }}</li></ul>
            </div>
        </template>
    </section>
</template>

<style scoped>
dt { color: #475569; }
dd { font-weight: 600; overflow-wrap: anywhere; }
</style>
