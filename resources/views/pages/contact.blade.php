@extends('layouts.app')

@section('title', 'Kontak Kami')

@section('description', $content('seo_description'))

@section('content')

    @php
        $contactImage = $content->image('hero_image');
        $contactEmail = $content('contact_email');
        $mapQuery = filled($content('location_map_query')) ? $content('location_map_query') : $content('contact_address');
    @endphp

    {{-- Contact Hero --}}
    <section class="relative overflow-hidden bg-white lg:min-h-[calc(100svh-5.75rem)]">

        {{-- Desktop Background Image --}}
        <div
            class="absolute inset-x-0 top-16 bottom-16 hidden overflow-hidden lg:block"
            aria-hidden="true"
        >
            <img
                src="{{ $contactImage }}"
                alt=""
                class="h-full w-full object-cover"
            >

            {{-- Desktop Light Gradient --}}
            <div
                class="absolute inset-0 bg-gradient-to-r from-white from-0% via-white/95 via-50% to-transparent to-100%"
            ></div>
        </div>

        {{-- Hero Content --}}
        <div
            class="container-site relative z-10 flex flex-col justify-center
                py-12 sm:py-16
                lg:min-h-[calc(100svh-5.75rem)] lg:py-0"
        >
            <div class="max-w-4xl">
                <div class="relative pl-5 sm:pl-8">

                    {{-- Vertical Accent --}}
                    <div
                        class="absolute bottom-0 left-0 top-0 w-px bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Eyebrow --}}
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        {{ $content('hero_label') }}
                    </span>

                    {{-- Heading --}}
                    <h1
                        class="font-display mt-5 max-w-3xl text-3xl font-bold
                            leading-[1.05] tracking-[-0.045em] text-secondary-dark
                            min-[390px]:text-4xl
                            sm:mt-6 sm:text-5xl
                            lg:mt-6 lg:text-7xl lg:leading-[1.02]"
                    >
                        {!! $content->rich('hero_title') !!}
                    </h1>

                    {{-- Description --}}
                    <p
                        class="font-body mt-5 max-w-2xl text-sm leading-7 text-slate-600
                            sm:mt-7 sm:text-base sm:leading-8"
                    >
                        {{ $content('hero_description') }}
                    </p>
                </div>
            </div>

            {{-- Mobile Image --}}
            <div class="mt-8 pl-5 sm:mt-10 sm:pl-8 lg:hidden">
                <div class="relative pr-3 pb-3">

                    {{-- Image --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-surface">
                        <img
                            src="{{ $contactImage }}"
                            alt="{{ $content('hero_image_alt') }}"
                            loading="eager"
                            fetchpriority="high"
                            class="h-full w-full object-cover object-center"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- Contact Information --}}
    <section class="bg-white">

        {{-- Section Divider --}}
        <div class="container-site">
            <div class="relative border-t border-slate-200"></div>
        </div>
        <div class="container-site py-16 sm:py-20 lg:py-24">
            <div class="grid gap-14 lg:grid-cols-12 lg:gap-0">

                {{-- Contact Details --}}
                <div class="lg:col-span-5 lg:pr-16">

                    {{-- Section Label --}}
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        {{ $content('info_label') }}
                    </span>

                    {{-- Heading --}}
                    <h2
                        class="font-display mt-4 text-3xl font-bold leading-[1.08] tracking-[-0.035em] text-secondary-dark sm:text-4xl"
                    >
                        {{ $content('info_title') }}
                    </h2>

                    {{-- Accent --}}
                    <div
                        class="mt-6 h-px w-16 bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Contact List --}}
                    <div class="mt-8">

                        {{-- Email --}}
                        <x-contact-card
                            type="email"
                            title="Email"
                            :value="$contactEmail"
                            :description="$content('email_description')"
                            :href="'mailto:'.$contactEmail"
                        />

                        {{-- Phone --}}
                        <x-contact-card
                            type="phone"
                            title="Telepon"
                            :value="$content('contact_phone')"
                            :description="$content('phone_description')"
                            :href="\App\Support\PageContents::telHref($content('contact_phone'))"
                        />

                        {{-- Address --}}
                        <x-contact-card
                            type="location"
                            title="Alamat"
                            :value="$content('contact_address')"
                            :description="$content('address_description')"
                            :href="\App\Support\PageContents::mapsSearchUrl($content('contact_address'))"
                            :border="false"
                        />
                    </div>
                </div>

                {{-- Location --}}
                <div class="lg:col-span-7 lg:pl-16">

                    {{-- Section Label --}}
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        {{ $content('location_label') }}
                    </span>

                    {{-- Heading --}}
                    <h2
                        class="font-display mt-4 text-3xl font-bold leading-[1.08] tracking-[-0.035em] text-secondary-dark sm:text-4xl"
                    >
                        {{ $content('location_title') }}
                    </h2>

                    {{-- Accent --}}
                    <div
                        class="mt-6 h-px w-16 bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Map --}}
                    <div
                        class="mt-8 overflow-hidden border border-slate-200 bg-slate-100"
                    >
                        <div class="aspect-[4/3] lg:aspect-[16/9.5]">
                            <iframe
                                src="{{ \App\Support\PageContents::mapsEmbedUrl($mapQuery) }}"
                                class="h-full w-full border-0 grayscale-[15%]"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Lokasi PT. Putra Maju Sukses"
                            ></iframe>
                        </div>
                    </div>

                    {{-- Address Caption --}}
                    <div class="mt-4 flex items-start gap-3">
                        <x-icons.maps
                            class="mt-0.5 h-4 w-4 shrink-0 text-primary"
                        />

                        <p
                            class="font-body max-w-xl text-xs leading-5 text-slate-500"
                        >
                            {{ rtrim($content('contact_address'), '.') }}.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact CTA --}}
    <section class="relative overflow-hidden bg-secondary-dark">

        {{-- Background Image --}}
        <div class="absolute inset-0">
            <img
                src="{{ $contactImage }}"
                alt=""
                class="h-full w-full object-cover"
                aria-hidden="true"
            >
            <div
                class="absolute inset-0 bg-secondary-dark/85"
                aria-hidden="true"
            ></div>
            <div
                class="absolute inset-0 bg-gradient-to-r from-secondary-dark via-secondary-dark/80 to-primary/30"
                aria-hidden="true"
            ></div>
        </div>
        <div class="container-site relative">
            <div
                class="flex flex-col gap-10 py-16 sm:py-20 lg:flex-row lg:items-center lg:justify-between lg:py-12"
            >
                <div class="max-w-3xl">
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary-vibrant"
                    >
                        {{ $content('cta_label') }}
                    </span>

                    <h2
                        class="font-display mt-4 text-3xl font-bold leading-[1.05] tracking-[-0.04em] text-white sm:text-4xl lg:text-5xl"
                    >
                        {{ $content('cta_title') }}
                    </h2>

                    <p
                        class="font-body mt-4 max-w-2xl text-sm leading-7 text-white/70 sm:text-base sm:leading-8"
                    >
                        {{ $content('cta_description') }}
                    </p>
                </div>
                <div class="shrink-0">
                    <x-button
                        href="mailto:{{ $contactEmail }}"
                        class="w-full bg-primary text-white hover:bg-primary-dark sm:w-auto"
                    >
                        {{ $content('cta_button') }}
                        <x-icons.arrow-right class="h-4 w-4" />
                    </x-button>
                </div>
            </div>
        </div>
    </section>
@endsection