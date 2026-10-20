@extends('layouts.app')

@section('title', 'Tentang Kami')

@section('description', $content('seo_description'))

@section('content')
    {{-- Hero Section --}}
    <section class="bg-white">
        <div class="container-site">
            <div class="relative border-b border-slate-200">

                {{-- Hero Top Label --}}
                <div class="pt-16 sm:pt-20 lg:pt-20">
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        {{ $content('hero_label') }}
                    </span>
                </div>

                {{-- Hero Content --}}
                <div
                    class="grid gap-10 pb-14 pt-8 lg:grid-cols-12 lg:gap-12 lg:pb-16 lg:pt-8"
                >
                    {{-- Content (Kiri) --}}
                    <div class="lg:col-span-7">

                        <h1
                            class="font-display max-w-3xl text-[3rem] font-bold leading-[0.96] tracking-[-0.05em] text-secondary-dark sm:text-[3.5rem] lg:text-[4rem] xl:text-[4.35rem]"
                        >
                            {{ $content('hero_title_line1') }}
                            <br>
                            <span class="text-primary">
                                {{ $content('hero_title_line2') }}
                            </span>
                            <br>
                            {{ $content('hero_title_line3') }}
                        </h1>
                        <p
                            class="font-body mt-7 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                        >
                            {{ $content('hero_description') }}
                        </p>
                    </div>

                    {{-- Image (Kanan) --}}
                    <div class="relative mt-12 lg:col-span-5 lg:mt-0">
                        <div
                            class="relative mx-auto w-full max-w-[440px] lg:-translate-y-10"
                        >
                            {{-- Red background frame --}}
                            <div
                                class="absolute -right-3 -top-3 h-[calc(100%-2rem)] w-[calc(100%-1rem)] bg-primary"
                                aria-hidden="true"
                            ></div>

                            {{-- Secondary outer frame --}}
                            <div
                                class="absolute -bottom-3 -left-3 h-full w-full border-2 border-primary/30"
                                aria-hidden="true"
                            ></div>

                            {{-- Photo --}}
                            <div
                                class="relative z-10 aspect-[4/3] overflow-hidden bg-slate-100"
                            >
                                <img
                                    src="{{ $content->image('hero_image') }}"
                                    alt="{{ $content('hero_image_alt') }}"
                                    class="h-full w-full object-cover"
                                >
                            </div>

                            {{-- Caption --}}
                            <div
                                class="relative z-10 flex items-center justify-between border-x border-b border-slate-200 bg-white px-4 py-3"
                            >
                                <span
                                    class="font-display text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                                >
                                    {{ $content('hero_caption_left') }}
                                </span>

                                <span
                                    class="font-display text-xs font-semibold text-primary"
                                >
                                    {{ $content('hero_caption_right') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tentang Perusahaan --}}
    <section class="bg-white">
        <div class="container-site">
            <div class="grid gap-12 border-b border-slate-200 py-20 lg:grid-cols-12 lg:gap-20 lg:py-24">

                {{-- Company Description (Kiri) --}}
                <div class="lg:col-span-7">
                    <div class="relative">
                        
                        {{-- Vertical Accent --}}
                        <div
                            class="absolute left-0 top-0 h-[198px] w-0.5 bg-primary"
                            aria-hidden="true"
                        ></div>

                        <div class="pl-7 sm:pl-8">
                            {{-- Label --}}
                            <span
                                class="font-display text-xs font-bold uppercase tracking-[0.16em] text-primary"
                            >
                                {{ $content('profile_label') }}
                            </span>

                            {{-- Main Heading --}}
                            <h2
                                class="font-display mt-7 max-w-2xl text-3xl font-semibold leading-[1.08] tracking-[-0.025em] text-secondary-dark sm:text-4xl lg:text-[2.75rem]"
                            >
                                <span class="block">
                                    {{ $content('profile_title_line1') }}
                                </span>

                                <span class="block text-primary">
                                    {{ $content('profile_title_line2') }}
                                </span>

                                <span class="block">
                                    {{ $content('profile_title_line3') }}
                                </span>
                            </h2>
                        </div>

                        {{-- Company Description --}}
                        <div class="mt-8 text-justify max-w-2xl pl-7 sm:pl-8">
                            <p
                                class="font-body text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                            >
                                {{ $content('profile_paragraph_1') }}
                            </p>
                            @if (filled($content('profile_paragraph_2')))
                                <p
                                    class="font-body mt-6 text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                                >
                                    {{ $content('profile_paragraph_2') }}
                                </p>
                            @endif
                            @if (filled($content('profile_paragraph_3')))
                                <p
                                    class="font-body mt-6 text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                                >
                                    {{ $content('profile_paragraph_3') }}
                                </p>
                            @endif
                            @if (filled($content('profile_paragraph_4')))
                                <p
                                    class="font-body mt-6 text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                                >
                                    {{ $content('profile_paragraph_4') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Company Detail (Kanan) --}}
                <div class="lg:col-span-5">
                    <div class="overflow-hidden bg-slate-100">
                        <img
                            src="{{ $content->image('profile_image') }}"
                            alt="{{ $content('profile_image_alt') }}"
                            class="h-full min-h-[280px] w-full object-cover"
                        >
                    </div>
                    <div class="mt-6 border border-slate-300 bg-white px-7 sm:px-8">
                        <div class="divide-y divide-slate-200">
                            <div class="py-5">
                                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ $content('facts_1_label') }}
                                </span>
                                <p class="font-body mt-2 text-sm text-secondary-dark">
                                    {{ $content('facts_1_value') }}
                                </p>
                            </div>
                            <div class="py-5">
                                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ $content('facts_2_label') }}
                                </span>
                                <p class="font-body mt-2 text-sm leading-6 text-secondary-dark">
                                    {{ $content('facts_2_value') }}
                                </p>
                            </div>
                            <div class="py-5">
                                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ $content('facts_3_label') }}
                                </span>
                                <p class="font-body mt-2 text-sm text-secondary-dark">
                                    {{ $content('facts_3_value') }}
                                </p>
                            </div>
                            <div class="py-5">
                                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                    {{ $content('facts_4_label') }}
                                </span>
                                <p class="font-body mt-2 text-sm leading-6 text-secondary-dark">
                                    {{ $content('facts_4_value') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Visi & Misi --}}
    <section class="border-t border-slate-200 bg-surface">

        {{-- Section Heading --}}
        <div class="border-y border-primary bg-secondary-dark">
            <div class="container-site py-10 sm:py-12 lg:py-14">
                <div
                    class="mx-auto mb-4 h-0.5 w-200 max-w-full bg-primary-alt"
                ></div>
                <div class="mx-auto max-w-3xl text-center">
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary-vibrant"
                    >
                        {{ $content('direction_label') }}
                    </span>
                    <p
                        class="font-display mt-3 text-base font-medium leading-relaxed tracking-[-0.01em] text-white/70 sm:text-lg"
                    >
                        {{ $content('direction_tagline_before') }}
                        <span class="text-primary-alt">
                            {{ $content('direction_tagline_highlight') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="container-site pt-7 pb-20 sm:pt-8 sm:pb-24 lg:pt-9 lg:pb-28">
            <div class="grid gap-4 lg:grid-cols-2">

                {{-- Vision Section --}}
                <article
                    class="relative border border-slate-300 bg-white px-6 py-6 sm:px-8 sm:py-7 lg:px-8 lg:py-8"
                >
                    {{-- Red top accent --}}
                    <div
                        class="absolute inset-x-0 top-0 h-0.5 bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Title --}}
                    <div class="text-center">
                        <span
                            class="font-display text-xs font-bold uppercase tracking-[0.16em] text-primary"
                        >
                            {{ $content('vision_label') }}
                        </span>
                    </div>

                    {{-- Content --}}
                    <div class="mx-auto mt-6 max-w-xl">
                        <h3
                            class="font-display text-xl font-semibold leading-[1.18] tracking-[-0.025em] text-secondary-dark sm:text-2xl"
                        >
                            {{ $content('vision_title') }}
                        </h3>
                        <div
                            class="mt-4 h-px w-10 bg-primary"
                            aria-hidden="true"
                        ></div>
                        <p
                            class="font-body mt-4 text-sm leading-6 text-slate-500"
                        >
                            {{ $content('vision_text') }}
                        </p>
                    </div>
                </article>

                {{-- Mission Section --}}
                <article
                    class="relative border border-slate-300 bg-white px-6 py-6 sm:px-8 sm:py-7 lg:px-8 lg:py-8"
                >
                    {{-- Red top accent --}}
                    <div
                        class="absolute inset-x-0 top-0 h-0.5 bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Title --}}
                    <div class="text-center">
                        <span
                            class="font-display text-xs font-bold uppercase tracking-[0.16em] text-primary"
                        >
                            {{ $content('mission_label') }}
                        </span>
                    </div>

                    {{-- Mission List: jumlah butir mengikuti isi dari admin --}}
                    <div class="mt-4">
                        @foreach ($content->list('mission_items') as $mission)
                            <div
                                class="flex items-start gap-3 border-t border-slate-200 py-3.5 last:border-b"
                            >
                                <span
                                    class="mt-[8px] h-1.5 w-1.5 shrink-0 rounded-full bg-primary"
                                    aria-hidden="true"
                                ></span>

                                <p
                                    class="font-body text-sm leading-6 text-secondary-dark"
                                >
                                    {{ $mission }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- Tujuan Perusahaan --}}
    <section class="bg-white">
        <div class="container-site">
            <div class="border-y border-slate-200 py-14 sm:py-16 lg:py-20">

                {{-- Heading --}}
                <div class="max-w-3xl">
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        {{ $content('objectives_label') }}
                    </span>
                    <h2
                        class="font-display mt-5 max-w-2xl text-4xl font-bold leading-[1.05] tracking-[-0.04em] text-secondary-dark sm:text-[2.75rem] lg:text-5xl"
                    >
                        {{ $content('objectives_title_before') }}
                        <span class="text-primary">
                            {{ $content('objectives_title_highlight') }}
                        </span>
                    </h2>
                </div>

                {{-- Objective Grid: kartu bertambah ke bawah mengikuti isi dari admin --}}
                <div class="mt-10 grid grid-cols-1 gap-x-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($content->cards('objective_items') as $objective)
                        <article class="border-t border-slate-300 py-7 sm:py-8">
                            <h3
                                class="font-display text-lg font-semibold tracking-tight text-secondary-dark sm:text-xl"
                            >
                                {{ $objective['title'] }}
                            </h3>
                            <p
                                class="font-body mt-3 max-w-sm text-sm leading-7 text-slate-600"
                            >
                                {{ $objective['text'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
        <section class="bg-surface">
            <div class="container-site">
                <div class="border-y border-slate-200 py-14 sm:py-16 lg:py-20">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span
                                class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                            >
                                {{ $content('cta_label') }}
                            </span>
                            <h2
                                class="font-display mt-3 text-2xl font-bold tracking-tight text-secondary-dark sm:text-3xl"
                            >
                                {{ $content('cta_title') }}
                            </h2>
                            <p
                                class="font-body mt-2 max-w-xl text-sm leading-7 text-slate-600"
                            >
                                {{ $content('cta_description') }}
                            </p>
                        </div>
                        <x-button href="{{ route('services') }}">
                            {{ $content('cta_button') }}
                            <x-icons.arrow-right class="h-4 w-4" />
                        </x-button>
                    </div>
                </div>
            </div>
        </section>
    </section>
@endsection