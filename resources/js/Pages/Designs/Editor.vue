<script setup>
import axios from 'axios';
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { notify, confirmAction } from '../../notifications';
import { validPreview } from '../../previewGeometry';
import AppLayout from '../../Layouts/AppLayout.vue';
import CraneForm from '../../Components/CraneForm.vue';
import CraneDrawing from '../../Components/CraneDrawing.vue';
import TechnicalSummary from '../../Components/TechnicalSummary.vue';

const props = defineProps({ limits: Object, previewUrl: String, designId: String });
const defaults = {
    name: 'Crane 001', crane_type: 'single_girder', capacity: 5, span: 15000,
    lifting_height: 6000, girder_depth: 500, girder_width: 300, end_carriage_length: 2500,
};
const keys = Object.keys(defaults);
const form = reactive({ ...defaults });
const saved = ref({ ...defaults });
const previewInputs = ref(null);
const designId = ref(props.designId);
const geometry = ref(null);
const view = ref('front');
const pending = ref(false);
const saving = ref(false);
const loading = ref(!!designId.value);
const loadError = ref('');
const errors = ref({});
const error = ref('');
const resetVersion = ref(0);
const lifetime = new AbortController();
let activeRequest;
let removeGuard;
let confirmingLeave = false;
let allowLeave = false;

function matches(inputs) {
    return keys.every(key => String(form[key]) === String(inputs[key]));
}
const dirty = computed(() => !matches(saved.value));
const outdated = computed(() => !!previewInputs.value && !matches(previewInputs.value));
const title = computed(() => designId.value ? 'Edit Design' : 'New Design');

function restore(record) {
    saved.value = Object.fromEntries(keys.map(key => [key, record[key]]));
    Object.assign(form, saved.value);
}
async function reopen() {
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await axios.get('/api/designs/' + designId.value, { signal: lifetime.signal });
        restore(data.data);
        generate();
    } catch (failure) {
        loadError.value = failure.response?.status === 404 ? 'Design not found. It may have been deleted.'
            : 'Could not load this design. Check your connection and try again.';
    } finally {
        loading.value = false;
    }
}
function cancelPreview() {
    activeRequest?.abort();
    activeRequest = undefined;
    pending.value = false;
}
function beforeUnload(event) {
    if (dirty.value) {
        event.preventDefault();
        event.returnValue = '';
    }
}
onMounted(() => {
    window.addEventListener('beforeunload', beforeUnload);
    removeGuard = router.on('before', event => {
        if (saving.value) return false;
        if (!dirty.value || allowLeave) return;
        if (!confirmingLeave) {
            confirmingLeave = true;
            const visit = event.detail.visit;
            confirmAction('Discard unsaved changes?', 'Your edits will be lost if you leave this design.', 'Discard changes')
                .then(({ isConfirmed }) => {
                    if (isConfirmed && !lifetime.signal.aborted) {
                        allowLeave = true;
                        try { router.visit(visit.url, visit); } finally { allowLeave = false; }
                    }
                }).finally(() => { confirmingLeave = false; });
        }
        return false;
    });
    if (designId.value) reopen();
});
onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', beforeUnload);
    removeGuard?.();
    lifetime.abort();
    cancelPreview();
});

function edit(key, value) {
    cancelPreview();
    form[key] = value;
    delete errors.value[key];
    error.value = '';
}
function reset() {
    cancelPreview();
    Object.assign(form, saved.value);
    errors.value = {};
    error.value = '';
    geometry.value = previewInputs.value = null;
    view.value = 'front';
    resetVersion.value++;
    if (designId.value) generate();
}
async function save() {
    if (saving.value) return;
    cancelPreview();
    saving.value = true;
    error.value = '';
    errors.value = {};
    try {
        const creating = !designId.value;
        const payload = { ...form };
        const config = { signal: lifetime.signal };
        const { data } = creating ? await axios.post('/api/designs', payload, config)
            : await axios.put('/api/designs/' + designId.value, payload, config);
        if (!data?.data?.id || !keys.every(key => data.data[key] != null)) throw new Error();
        designId.value = String(data.data.id);
        restore(data.data);
        if (creating) {
            router.replace({
                url: '/designs/' + designId.value + '/edit',
                props: current => ({ ...current, designId: designId.value }),
                preserveState: true,
            });
        }
        notify('success', creating ? 'Design saved successfully.' : 'Design updated successfully.');
        if (!geometry.value || outdated.value) generate();
    } catch (failure) {
        if (lifetime.signal.aborted) return;
        if (failure.response?.status === 422) {
            errors.value = failure.response.data.errors;
            notify('error', 'Review the highlighted inputs and save again.');
        } else if (failure.response?.status === 404) {
            notify('error', 'Update failed. This design no longer exists.');
        } else {
            notify('error', (designId.value ? 'Update' : 'Save') + ' could not be confirmed. Check Saved Designs before retrying.');
        }
    } finally {
        saving.value = false;
    }
}
async function generate() {
    if (pending.value) return;
    const request = new AbortController();
    activeRequest = request;
    pending.value = true;
    errors.value = {};
    error.value = '';
    const inputs = { ...form };
    try {
        const response = await axios.post(props.previewUrl, inputs, { signal: request.signal });
        if (request !== activeRequest) return;
        const data = response.data?.data;
        if (!validPreview(data)) {
            error.value = 'The server returned incomplete preview data. Please generate again.';
            return;
        }
        geometry.value = data;
        previewInputs.value = inputs;
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
    <Head :title="title + ' · Crane Design Studio'" />
    <AppLayout>
        <h1 class="mb-3 text-3xl font-semibold tracking-tight">{{ title }}</h1>
        <p v-if="loading" role="status">Loading design…</p>
        <div v-else-if="loadError" role="alert">
            {{ loadError }} <button class="underline" @click="reopen">Retry</button>
        </div>
        <template v-else>
            <div class="mb-5 flex flex-wrap items-center gap-3 text-sm" role="status">
                <strong v-if="designId" class="min-w-0 break-all">{{ saved.name }}</strong>
                <span v-if="dirty" class="rounded bg-amber-100 px-2 py-1 text-amber-950">Unsaved Changes</span>
                <span v-else>{{ designId ? 'Saved' : 'New, unsaved design' }}</span>
            </div>
            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                <CraneForm :form="form" :limits="limits" :errors="errors" :pending="pending" :reset-version="resetVersion"
                    :saving="saving" :editing="!!designId" @edit="edit" @generate="generate" @reset="reset" @save="save" />
                <div class="min-w-0 space-y-5">
                    <CraneDrawing v-model:view="view" :geometry="geometry" :pending="pending" :error="error" :outdated="outdated" />
                    <TechnicalSummary :geometry="geometry" />
                </div>
            </div>
        </template>
    </AppLayout>
</template>
