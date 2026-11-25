<script setup>
import { reactive, watch } from 'vue';
import { craneTypeLabel, dimensions } from '../craneFields';

const props = defineProps({
    form: Object, limits: Object, errors: Object, pending: Boolean, saving: Boolean, editing: Boolean, resetVersion: Number,
});
const emit = defineEmits(['edit', 'generate', 'save', 'reset']);
const localErrors = reactive({});
watch(() => props.resetVersion, () => {
    for (const key of Object.keys(localErrors)) delete localErrors[key];
});

function validate(input) {
    localErrors[input.name] = input.validationMessage;
}
function edit(event) {
    const input = event.target;
    validate(input);
    emit('edit', input.name, input.value);
}
function message(key) {
    return props.errors[key]?.[0] || localErrors[key];
}
</script>

<template>
    <form class="rounded-lg border border-slate-300 bg-white p-5 sm:p-6"
        @submit.prevent="emit('generate')" @invalid.capture="validate($event.target)">
        <fieldset :disabled="saving" class="min-w-0 space-y-5">
        <h2 class="text-lg font-semibold">Design inputs</h2>
        <div>
            <label for="name">Design name</label>
            <input id="name" name="name" required :maxlength="limits.name_max_length" :value="form.name"
                :aria-invalid="!!message('name')" :aria-describedby="message('name') ? 'name-error' : undefined"
                @input="edit" @blur="validate($event.target)">
            <p v-if="message('name')" id="name-error" class="field-error">{{ message('name') }}</p>
        </div>
        <div>
            <label for="crane_type">Crane type</label>
            <select id="crane_type" name="crane_type" required :value="form.crane_type" @change="edit"
                :aria-invalid="!!message('crane_type')" :aria-describedby="message('crane_type') ? 'type-error' : undefined">
                <option v-for="type in limits.crane_types" :key="type" :value="type">{{ craneTypeLabel(type) }}</option>
            </select>
            <p v-if="message('crane_type')" id="type-error" class="field-error">{{ message('crane_type') }}</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div v-for="(label, key) in { capacity: 'Required capacity', ...dimensions }" :key="key">
                <label :for="key">{{ label }} <span class="font-normal">({{ key === 'capacity' ? 'metric tonnes' : 'mm' }})</span></label>
                <input :id="key" :name="key" type="number" required :value="form[key]"
                    :min="(limits.dimensions[key] ?? limits.capacity).min"
                    :max="(limits.dimensions[key] ?? limits.capacity).max"
                    :step="key === 'capacity' ? 10 ** -limits.capacity.decimal_places : 1"
                    :aria-invalid="!!message(key)" :aria-describedby="message(key) ? key + '-error' : undefined"
                    @input="edit" @blur="validate($event.target)">
                <p v-if="message(key)" :id="key + '-error'" class="field-error">{{ message(key) }}</p>
            </div>
        </div>
        <p class="text-sm text-slate-600">Default values are illustrative design inputs.</p>
        <div class="flex flex-wrap items-center gap-4 border-t border-slate-200 pt-5">
            <button type="submit" :disabled="pending" class="rounded-md bg-teal-800 hover:bg-teal-900 px-4 py-2 font-semibold text-white disabled:opacity-60">
                {{ pending ? 'Generating…' : 'Generate Preview' }}
            </button>
            <button type="button" class="rounded-md border border-teal-800 hover:bg-teal-50 hover:text-teal-950 px-4 py-2 font-semibold text-teal-900 disabled:opacity-60"
                :disabled="saving" @click="emit('save')">{{ saving ? 'Saving…' : editing ? 'Save Changes' : 'Save Design' }}</button>
            <button type="button" class="rounded hover:bg-slate-100 hover:text-slate-950 px-2 py-2 text-sm font-semibold" @click="emit('reset')">Reset Form</button>
        </div>
        </fieldset>
    </form>
</template>

<style scoped>
@reference "../../css/app.css";
label { @apply mb-1 block text-sm font-medium; }
input, select { @apply w-full rounded-md border border-slate-400 bg-white px-3 py-2; }
[aria-invalid="true"] { @apply border-red-700; }
.field-error { @apply mt-1 text-sm text-red-800; }
</style>
