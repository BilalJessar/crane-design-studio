<script setup>
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { confirmAction, notify } from '../../notifications';
import { craneTypeLabel } from '../../craneFields';

const designs = ref([]);
const loading = ref(true);
const loadError = ref('');
const deleting = ref(null);
const lifetime = new AbortController();
const busy = ref(false);
const heading = ref();
onBeforeUnmount(() => lifetime.abort());
const dateFormat = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' });

async function load() {
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await axios.get('/api/designs', { signal: lifetime.signal });
        if (!Array.isArray(data?.data)) throw new Error();
        designs.value = data.data;
    } catch {
        loadError.value = 'Could not load saved designs. Check your connection and try again.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

async function remove(design, trigger) {
    if (busy.value) return;
    busy.value = true;
    try {
        const { isConfirmed } = await confirmAction('Delete this design?',
            'Delete "' + design.name + '"? This cannot be undone.', 'Yes, delete it!');
        if (!isConfirmed || lifetime.signal.aborted) return;
        deleting.value = design.id;
        await axios.delete('/api/designs/' + design.id, { signal: lifetime.signal });
        designs.value = designs.value.filter(item => item.id !== design.id);
        await nextTick();
        heading.value?.focus();
        notify('success', 'Design deleted successfully.');
    } catch (failure) {
        if (lifetime.signal.aborted) return;
        notify('error', failure.response?.status === 404
            ? 'Design no longer exists. Reload Saved Designs to refresh the list.'
            : 'Deletion could not be confirmed. Check your connection and refresh before retrying.');
    } finally {
        busy.value = false;
        deleting.value = null;
        await nextTick();
        if (trigger.isConnected) trigger.focus();
    }
}
</script>

<template>
    <Head title="Saved Designs · Crane Design Studio" />
    <AppLayout>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 ref="heading" tabindex="-1" class="text-3xl font-semibold tracking-tight">Saved Designs</h1>
            <Link href="/" class="rounded-md bg-teal-800 hover:bg-teal-900 px-4 py-2 font-semibold text-white">New Design</Link>
        </div>
        <p v-if="loading" role="status">Loading saved designs…</p>
        <div v-else-if="loadError" role="alert">{{ loadError }} <button class="underline" @click="load">Retry</button></div>
        <p v-else-if="!designs.length" role="status">No saved designs yet.</p>
        <ul v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <li v-for="design in designs" :key="design.id" class="min-w-0 rounded-lg border border-slate-300 bg-white p-5">
                <h2 class="break-words text-lg font-semibold">{{ design.name }}</h2>
                <p class="mb-4 text-sm text-slate-600">{{ craneTypeLabel(design.crane_type) }}</p>
                <dl class="grid grid-cols-[1fr_auto] gap-2 text-sm">
                    <dt>Required capacity</dt><dd>{{ design.capacity }} metric tonnes</dd>
                    <dt>Span</dt><dd>{{ design.span }} mm</dd>
                    <dt>Required lifting height</dt><dd>{{ design.lifting_height }} mm</dd>
                    <dt>Last updated</dt><dd><time :datetime="design.updated_at">{{ dateFormat.format(new Date(design.updated_at)) }}</time></dd>
                </dl>
                <div class="mt-4 flex gap-4 border-t border-slate-200 pt-3 font-semibold">
                    <Link :href="'/designs/' + design.id + '/edit'" :aria-label="'Edit ' + design.name" class="rounded-md px-3 py-1.5 text-teal-800 hover:bg-teal-50 hover:text-teal-950">Edit</Link>
                    <button :aria-label="'Delete ' + design.name" :disabled="!!deleting" class="rounded-md px-3 py-1.5 text-red-700 hover:bg-red-50 hover:text-red-800 disabled:opacity-50"
                        @click="remove(design, $event.currentTarget)">{{ deleting === design.id ? 'Deleting…' : 'Delete' }}</button>
                </div>
            </li>
        </ul>
    </AppLayout>
</template>
