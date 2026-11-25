<script setup>
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { craneTypeLabel } from '../../craneFields';

const designs = ref([]);
const loading = ref(true);
const loadError = ref('');
const deleting = ref(null);
const deleteError = ref('');
const notice = ref('');
const dateFormat = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' });

async function load() {
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await axios.get('/api/designs');
        if (!Array.isArray(data?.data)) throw new Error();
        designs.value = data.data;
    } catch {
        loadError.value = 'Could not load saved designs. Check your connection and try again.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

async function remove(design) {
    if (deleting.value || !window.confirm('Delete “' + design.name + '”? This cannot be undone.')) return;
    deleting.value = design.id;
    notice.value = deleteError.value = '';
    try {
        await axios.delete('/api/designs/' + design.id);
        designs.value = designs.value.filter(item => item.id !== design.id);
        notice.value = 'Deleted “' + design.name + '”.';
    } catch (failure) {
        if (failure.response?.status === 404) {
            notice.value = '“' + design.name + '” no longer exists. Refreshing the list.';
            await load();
        } else {
            deleteError.value = 'Could not confirm deletion of “' + design.name + '”. Please try again.';
        }
    } finally {
        deleting.value = null;
    }
}
</script>

<template>
    <Head title="Saved Designs · Crane Design Studio" />
    <AppLayout>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-3xl font-semibold tracking-tight">Saved Designs</h1>
            <Link href="/" class="rounded-md bg-teal-800 px-4 py-2 font-semibold text-white">New Design</Link>
        </div>
        <p v-if="notice" role="status" class="mb-4 text-teal-800">{{ notice }}</p>
        <p v-if="deleteError" role="alert" class="mb-4 text-red-800">{{ deleteError }}</p>
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
                    <Link :href="'/designs/' + design.id + '/edit'" :aria-label="'Edit ' + design.name" class="text-teal-800">Edit</Link>
                    <button :aria-label="'Delete ' + design.name" :disabled="!!deleting" class="text-red-800 disabled:opacity-50"
                        @click="remove(design)">{{ deleting === design.id ? 'Deleting…' : 'Delete' }}</button>
                </div>
            </li>
        </ul>
    </AppLayout>
</template>
