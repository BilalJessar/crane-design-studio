<script setup>
import { computed } from 'vue';

const props = defineProps({ shape: { type: Object, required: true } });
const g = computed(() => props.shape.geometry);
</script>

<template>
    <rect v-if="shape.type === 'rectangle'" :x="g.x" :y="g.y" :width="g.width" :height="g.height" />
    <line v-else-if="shape.type === 'line'" :x1="g.x1" :y1="g.y1" :x2="g.x2" :y2="g.y2" />
    <text v-else-if="shape.type === 'text'" :x="g.x" :y="g.y" :font-size="g.fontSize"
        :text-anchor="g.anchor">{{ g.text }}</text>
</template>
