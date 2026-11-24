<script setup>
import axios from 'axios';
import { validPreview } from '../../previewGeometry';
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, reactive, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import CraneForm from '../../Components/CraneForm.vue';
import CraneDrawing from '../../Components/CraneDrawing.vue';
import TechnicalSummary from '../../Components/TechnicalSummary.vue';

const props = defineProps({ limits: Object, previewUrl: String });
const defaults = {
    name: 'Crane 001', crane_type: 'single_girder', capacity: 5, span: 15000,
    lifting_height: 6000, girder_depth: 500, girder_width: 300, end_carriage_length: 2500,
};
const form = reactive({ ...defaults });
const geometry = ref(null);
const view = ref('front');
const pending = ref(false);
const outdated = ref(false);
const errors = ref({});
const error = ref('');
const resetVersion = ref(0);
let activeRequest;

function cancel() {
    activeRequest?.abort();
    activeRequest = undefined;
    pending.value = false;
}
onBeforeUnmount(cancel);

function edit(key, value) {
    cancel();
    form[key] = value;
    delete errors.value[key];
    error.value = Object.keys(errors.value).length ? 'Review the highlighted inputs and generate again.' : '';
    outdated.value = !!geometry.value;
}
function reset() {
    cancel();
    Object.assign(form, defaults);
    errors.value = {};
    error.value = '';
    geometry.value = null;
    outdated.value = false;
    view.value = 'front';
    resetVersion.value++;
}
async function generate() {
    if (pending.value) return;
    const request = new AbortController();
    activeRequest = request;
    pending.value = true;
    errors.value = {};
    error.value = '';
    try {
        const response = await axios.post(props.previewUrl, { ...form }, {
            signal: request.signal,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        });
        if (request !== activeRequest) return;
        const data = response.data?.data;
        if (!validPreview(data)) {
            error.value = 'The server returned incomplete preview data. Please generate again.';
            return;
        }
        geometry.value = data;
        outdated.value = false;
    } catch (failure) {
        if (activeRequest !== request) return;
        if (failure.response?.status === 422 && failure.response.data?.errors) {
            errors.value = failure.response.data.errors;
            error.value = 'Review the highlighted inputs and generate again.';
        } else {
            error.value = failure.response ? 'The server could not generate a preview. Please try again.'
                : 'Could not reach the server. Check your connection and try again.';
        }
    } finally {
        if (activeRequest === request) {
            activeRequest = undefined;
            pending.value = false;
        }
    }
}
</script>

<template>
    <Head title="New Design · Crane Design Studio" />
    <AppLayout>
        <h1 class="mb-5 text-3xl font-semibold tracking-tight">New design</h1>
        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <CraneForm :form="form" :limits="limits" :errors="errors" :pending="pending" :reset-version="resetVersion"
                @edit="edit" @generate="generate" @reset="reset" />
            <div class="min-w-0 space-y-5">
                <CraneDrawing v-model:view="view" :geometry="geometry" :pending="pending" :error="error" :outdated="outdated" />
                <TechnicalSummary :geometry="geometry" />
            </div>
        </div>
    </AppLayout>
</template>
