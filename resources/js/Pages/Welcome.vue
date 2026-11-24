<script setup>
import { Head } from '@inertiajs/vue3';
</script>
<template>
    <Head title="Crane Design Studio" />
    <main class="min-h-screen bg-slate-100 text-slate-900">
        <header class="bg-slate-900 px-8 py-5 text-white font-semibold">Crane Design Studio</header>
        <section class="mx-auto max-w-4xl px-6 py-16">
            <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Starter workspace</p>
                <h1 class="mt-3 text-3xl font-bold">Welcome to Crane Design Studio</h1>
                <p class="mt-3 text-slate-600">Laravel, Inertia and Vue are connected. Crane design features will be implemented next.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <span v-for="tech in ['Laravel', 'Inertia', 'Vue 3', 'Tailwind CSS', 'SQLite', 'Docker']" :key="tech" class="rounded-md bg-slate-100 px-3 py-2 text-sm">{{ tech }}</span>
                </div>
            </div>
        </section>
    </main>
</template>
