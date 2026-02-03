<?php
/**
 * Product Showcase Template - Vite + Tailwind
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Affiliate Product Showcase -->
<div class="aps-wrapper">
    <div class="aps-max-w-7xl aps-mx-auto aps-px-4 sm:aps-px-6 lg:aps-px-8 aps-py-8">
        <div class="aps-flex aps-flex-col lg:aps-flex-row aps-gap-8">
            
            <!-- Sidebar -->
            <aside class="aps-w-full lg:aps-w-60 aps-flex-shrink-0 aps-space-y-7">
                <!-- Search -->
                <div class="aps-relative">
                    <i data-lucide="search" class="aps-absolute aps-left-4 aps-top-1/2 -aps-translate-y-1/2 aps-w-4 aps-h-4 aps-text-gray-400"></i>
                    <input 
                        type="text" 
                        placeholder="Search tools..." 
                        class="aps-w-full aps-pl-11 aps-pr-4 aps-py-3 aps-bg-white aps-border aps-border-gray-200 aps-rounded-xl aps-text-sm focus:aps-outline-none focus:aps-ring-2 focus:aps-ring-blue-500 focus:aps-border-transparent aps-transition-all"
                    >
                </div>

                <!-- Filter Header -->
                <div class="aps-flex aps-justify-between aps-items-center aps-pb-4 aps-border-b aps-border-gray-200">
                    <span class="aps-text-sm aps-font-semibold aps-text-gray-700 aps-uppercase aps-tracking-wider">Filter Tools</span>
                    <button id="clearAll" class="aps-text-sm aps-font-medium aps-text-blue-500 hover:aps-text-blue-600 aps-transition-colors">Clear All</button>
                </div>

                <!-- Categories -->
                <div class="aps-space-y-3">
                    <span class="aps-text-xs aps-font-semibold aps-text-gray-400 aps-uppercase aps-tracking-wider">Category</span>
                    <div class="aps-flex aps-flex-wrap aps-gap-2 custom-scrollbar aps-overflow-x-auto aps-pb-1">
                        <button class="tab tab-active aps-px-4 aps-py-2 aps-rounded-full aps-text-sm aps-font-medium aps-border aps-border-gray-200 aps-bg-gray-100 aps-text-gray-600 hover:aps-bg-gray-200 aps-transition-all aps-whitespace-nowrap" data-category="all">All Tools</button>
                        <button class="tab aps-px-4 aps-py-2 aps-rounded-full aps-text-sm aps-font-medium aps-border aps-border-gray-200 aps-bg-gray-100 aps-text-gray-600 hover:aps-bg-gray-200 aps-transition-all aps-whitespace-nowrap" data-category="hosting">Hosting</button>
                        <button class="tab aps-px-4 aps-py-2 aps-rounded-full aps-text-sm aps-font-medium aps-border aps-border-gray-200 aps-bg-gray-100 aps-text-gray-600 hover:aps-bg-gray-200 aps-transition-all aps-whitespace-nowrap" data-category="ai">AI Tools</button>
                        <button class="tab aps-px-4 aps-py-2 aps-rounded-full aps-text-sm aps-font-medium aps-border aps-border-gray-200 aps-bg-gray-100 aps-text-gray-600 hover:aps-bg-gray-200 aps-transition-all aps-whitespace-nowrap" data-category="seo">SEO Tools</button>
                        <button class="tab aps-px-4 aps-py-2 aps-rounded-full aps-text-sm aps-font-medium aps-border aps-border-gray-200 aps-bg-gray-100 aps-text-gray-600 hover:aps-bg-gray-200 aps-transition-all aps-whitespace-nowrap" data-category="marketing">Marketing Tools</button>
                    </div>
                </div>

                <!-- Tags -->
                <div class="aps-space-y-3">
                    <span class="aps-text-xs aps-font-semibold aps-text-gray-400 aps-uppercase aps-tracking-wider">Tags</span>
                    <div class="aps-flex aps-flex-wrap aps-gap-2 custom-scrollbar aps-overflow-x-auto aps-pb-1">
                        <button class="tag tag-active aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="featured">
                            <span>⭐</span> Featured
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="writing">
                            <span>✍️</span> Writing
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="video">
                            <span>🎥</span> Video
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="audio">
                            <span>🎤</span> Audio
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="design">
                            <span>🎨</span> Design
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="freetrial">
                            <span>🆓</span> Free Trial
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="nocc">
                            <span>💳</span> No CC
                        </button>
                        <button class="tag aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="freeforever">
                            <span>🎁</span> Free Forever
                        </button>
                        <button class="tag tag-active aps-px-3 aps-py-1.5 aps-rounded-full aps-text-xs aps-font-medium aps-border aps-border-gray-200 aps-bg-white aps-text-gray-600 hover:aps-bg-gray-50 aps-transition-all aps-flex aps-items-center aps-gap-1.5" data-tag="verified">
                            <span>✅</span> Verified
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="aps-flex-1">
                <!-- Header -->
                <div class="aps-flex aps-justify-end aps-mb-6">
                    <div class="aps-relative">
                        <button class="sort-btn aps-flex aps-items-center aps-justify-between aps-gap-3 aps-px-4 aps-py-2.5 aps-bg-white aps-border aps-border-gray-200 aps-rounded-lg aps-text-sm aps-font-medium aps-text-gray-700 hover:aps-bg-gray-50 aps-transition-all aps-min-w-[160px]">
                            <div class="aps-flex aps-items-center aps-gap-2">
                                <span class="aps-text-gray-400 aps-font-normal">Sort by</span>
                                <span class="sort-value">Featured</span>
                            </div>
                            <i data-lucide="chevron-down" class="aps-w-4 aps-h-4 aps-text-gray-400"></i>
                        </button>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="aps-grid aps-grid-cols-1 md:aps-grid-cols-2 xl:aps-grid-cols-3 aps-gap-6">
                    
                    <!-- Card 1 -->
                    <article class="tool-card card-hover aps-bg-white aps-rounded-2xl aps-border aps-border-gray-200 aps-overflow-hidden aps-relative">
                        <div class="aps-absolute aps-top-3 aps-left-3 aps-z-20">
                            <span class="aps-inline-flex aps-items-center aps-gap-1.5 aps-px-3 aps-py-1.5 aps-bg-indigo-500 aps-text-white aps-text-[11px] aps-font-bold aps-uppercase aps-tracking-wider aps-rounded-full aps-shadow-md">
                                <i data-lucide="star" class="aps-w-3 aps-h-3 aps-fill-current"></i>
                                Featured
                            </span>
                        </div>
                        
                        <div class="card-image gradient-pink aps-h-40 aps-relative aps-flex aps-items-center aps-justify-center aps-text-white/90 aps-font-medium aps-text-sm">
                            <button class="bookmark-icon aps-absolute aps-top-3 aps-left-3 aps-w-9 aps-h-9 aps-bg-white aps-rounded-full aps-flex aps-items-center aps-justify-center aps-shadow-md hover:aps-scale-110 aps-transition-transform aps-z-10">
                                <i data-lucide="bookmark" class="aps-w-4 aps-h-4 aps-text-gray-500"></i>
                            </button>
                            <span class="aps-drop-shadow-md">Preview</span>
                        </div>

                        <div class="aps-p-5">
                            <div class="aps-flex aps-justify-between aps-items-start aps-mb-3">
                                <h3 class="aps-text-lg aps-font-bold aps-text-gray-900">New Featured Tool</h3>
                                <div class="aps-text-right">
                                    <span class="aps-block aps-text-xs aps-text-gray-400 aps-line-through">$39.99/mo</span>
                                    <div class="aps-flex aps-items-baseline aps-justify-end">
                                        <span class="aps-text-xl aps-font-bold aps-text-gray-900">$19.99</span>
                                        <span class="aps-text-xs aps-text-gray-500 aps-ml-0.5">/mo</span>
                                    </div>
                                    <span class="aps-inline-block aps-mt-1 aps-px-2.5 aps-py-1 aps-bg-emerald-100 aps-text-emerald-600 aps-text-[10px] aps-font-bold aps-rounded-full">50% OFF</span>
                                </div>
                            </div>

                            <p class="aps-text-sm aps-text-gray-600 aps-mb-3 aps-leading-relaxed">
                                Latest release with modern UX and improved analytics — featured to showcase new capabilities.
                            </p>

                            <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-sm aps-text-gray-600 aps-mb-4">
                                <i data-lucide="star" class="aps-w-3.5 aps-h-3.5 aps-text-amber-400 aps-fill-current"></i>
                                <span>Featured</span>
                            </div>

                            <div class="aps-flex aps-flex-wrap aps-gap-3 aps-mb-5">
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Realtime Analytics</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>AI Suggestions</span>
                                </div>
                            </div>

                            <div class="aps-pt-4 aps-border-t aps-border-gray-100">
                                <div class="aps-flex aps-items-center aps-justify-between aps-mb-4">
                                    <div class="aps-flex aps-items-center aps-gap-3">
                                        <div class="aps-flex aps-items-center">
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <span class="aps-ml-1.5 aps-text-sm aps-font-bold aps-text-gray-900">4.9/5</span>
                                        </div>
                                        <span class="aps-text-xs aps-text-gray-400">1,024 reviews</span>
                                    </div>
                                    <div class="aps-flex aps-items-center aps-gap-1 aps-px-2.5 aps-py-1 aps-bg-emerald-50 aps-text-emerald-600 aps-rounded-full aps-text-[11px] aps-font-semibold">
                                        <i data-lucide="users" class="aps-w-3 aps-h-3"></i>
                                        <span>1K+ users</span>
                                    </div>
                                </div>

                                <button class="aps-w-full aps-py-3.5 aps-bg-gray-900 aps-text-white aps-rounded-xl aps-text-sm aps-font-semibold hover:aps-bg-gray-800 aps-transition-colors aps-flex aps-items-center aps-justify-center aps-gap-2 group">
                                    Explore Now
                                    <i data-lucide="external-link" class="aps-w-4 aps-h-4 group-hover:aps-translate-x-0.5 group-hover:-aps-translate-y-0.5 aps-transition-transform"></i>
                                </button>
                            </div>
                        </div>
                    </article>

                    <!-- Card 2 -->
                    <article class="tool-card card-hover aps-bg-white aps-rounded-2xl aps-border aps-border-gray-200 aps-overflow-hidden aps-relative">
                        <div class="aps-absolute aps-top-3 aps-left-3 aps-z-20">
                            <span class="aps-inline-flex aps-items-center aps-gap-1.5 aps-px-3 aps-py-1.5 aps-bg-indigo-500 aps-text-white aps-text-[11px] aps-font-bold aps-uppercase aps-tracking-wider aps-rounded-full aps-shadow-md">
                                <i data-lucide="star" class="aps-w-3 aps-h-3 aps-fill-current"></i>
                                Featured
                            </span>
                        </div>
                        
                        <div class="aps-absolute aps-top-3 aps-right-3 aps-z-20">
                            <div class="aps-flex aps-items-center aps-gap-1.5 aps-px-2.5 aps-py-1.5 aps-bg-white/95 aps-backdrop-blur-sm aps-text-amber-500 aps-text-xs aps-font-semibold aps-rounded-full aps-shadow-sm">
                                <i data-lucide="eye" class="aps-w-3.5 aps-h-3.5"></i>
                                <span>412 viewed</span>
                            </div>
                        </div>

                        <div class="card-image gradient-cyan aps-h-40 aps-relative aps-flex aps-items-center aps-justify-center aps-text-white/90 aps-font-medium aps-text-sm">
                            <button class="bookmark-icon aps-absolute aps-top-3 aps-left-3 aps-w-9 aps-h-9 aps-bg-white aps-rounded-full aps-flex aps-items-center aps-justify-center aps-shadow-md hover:aps-scale-110 aps-transition-transform aps-z-10">
                                <i data-lucide="bookmark" class="aps-w-4 aps-h-4 aps-text-gray-500"></i>
                            </button>
                            <span class="aps-drop-shadow-md">Product Dashboard Preview</span>
                        </div>

                        <div class="aps-p-5">
                            <div class="aps-flex aps-justify-between aps-items-start aps-mb-3">
                                <h3 class="aps-text-lg aps-font-bold aps-text-gray-900 aps-flex aps-items-center aps-gap-2">
                                    <span class="aps-w-6 aps-h-6 aps-bg-orange-100 aps-text-orange-600 aps-rounded-md aps-flex aps-items-center aps-justify-center aps-text-xs">📤</span>
                                    SEMrush Pro
                                </h3>
                                <div class="aps-text-right">
                                    <span class="aps-block aps-text-xs aps-text-gray-400 aps-line-through">$229.95/mo</span>
                                    <div class="aps-flex aps-items-baseline aps-justify-end">
                                        <span class="aps-text-xl aps-font-bold aps-text-gray-900">$119</span>
                                        <span class="aps-text-xs aps-text-gray-500 aps-ml-0.5">/mo</span>
                                    </div>
                                    <span class="aps-inline-block aps-mt-1 aps-px-2.5 aps-py-1 aps-bg-emerald-100 aps-text-emerald-600 aps-text-[10px] aps-font-bold aps-rounded-full">48% OFF</span>
                                </div>
                            </div>

                            <p class="aps-text-sm aps-text-gray-600 aps-mb-3 aps-leading-relaxed">
                                The most accurate difficulty score in the industry. Find low-competition keywords and spy on competitors' traffic sources easily.
                            </p>

                            <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-sm aps-text-gray-600 aps-mb-4">
                                <i data-lucide="star" class="aps-w-3.5 aps-h-3.5 aps-text-amber-400 aps-fill-current"></i>
                                <span>Featured</span>
                            </div>

                            <div class="aps-flex aps-flex-wrap aps-gap-3 aps-mb-5">
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Keyword Research</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Competitor Analysis</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Site Audit</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-400">
                                    <i data-lucide="link" class="aps-w-3.5 aps-h-3.5"></i>
                                    <span>Traffic Analytics</span>
                                </div>
                            </div>

                            <div class="aps-pt-4 aps-border-t aps-border-gray-100">
                                <div class="aps-flex aps-items-center aps-justify-between aps-mb-4">
                                    <div class="aps-flex aps-items-center aps-gap-3">
                                        <div class="aps-flex aps-items-center">
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <span class="aps-ml-1.5 aps-text-sm aps-font-bold aps-text-gray-900">5.0/5</span>
                                        </div>
                                        <span class="aps-text-xs aps-text-gray-400">3,421 reviews</span>
                                    </div>
                                    <div class="aps-flex aps-items-center aps-gap-1 aps-px-2.5 aps-py-1 aps-bg-red-50 aps-text-red-600 aps-rounded-full aps-text-[11px] aps-font-semibold">
                                        <i data-lucide="users" class="aps-w-3 aps-h-3"></i>
                                        <span>10M+ users</span>
                                    </div>
                                </div>

                                <button class="aps-w-full aps-py-3.5 aps-bg-gray-900 aps-text-white aps-rounded-xl aps-text-sm aps-font-semibold hover:aps-bg-gray-800 aps-transition-colors aps-flex aps-items-center aps-justify-center aps-gap-2 group">
                                    Claim Discount
                                    <i data-lucide="external-link" class="aps-w-4 aps-h-4 group-hover:aps-translate-x-0.5 group-hover:-aps-translate-y-0.5 aps-transition-transform"></i>
                                </button>
                                <p class="aps-text-center aps-mt-3 aps-text-xs aps-text-gray-400">14-day free trial available</p>
                            </div>
                        </div>
                    </article>

                    <!-- Card 3 -->
                    <article class="tool-card card-hover aps-bg-white aps-rounded-2xl aps-border aps-border-gray-200 aps-overflow-hidden aps-relative">
                        <div class="aps-absolute aps-top-3 aps-left-3 aps-z-20">
                            <span class="aps-inline-flex aps-items-center aps-gap-1.5 aps-px-3 aps-py-1.5 aps-bg-indigo-500 aps-text-white aps-text-[11px] aps-font-bold aps-uppercase aps-tracking-wider aps-rounded-full aps-shadow-md">
                                <i data-lucide="star" class="aps-w-3 aps-h-3 aps-fill-current"></i>
                                Featured
                            </span>
                        </div>
                        
                        <div class="aps-absolute aps-top-3 aps-right-3 aps-z-20">
                            <div class="aps-flex aps-items-center aps-gap-1.5 aps-px-2.5 aps-py-1.5 aps-bg-white/95 aps-backdrop-blur-sm aps-text-amber-500 aps-text-xs aps-font-semibold aps-rounded-full aps-shadow-sm">
                                <i data-lucide="eye" class="aps-w-3.5 aps-h-3.5"></i>
                                <span>128 viewed</span>
                            </div>
                        </div>

                        <div class="card-image gradient-purple aps-h-40 aps-relative aps-flex aps-items-center aps-justify-center aps-text-white/90 aps-font-medium aps-text-sm">
                            <button class="bookmark-icon aps-absolute aps-top-3 aps-left-3 aps-w-9 aps-h-9 aps-bg-white aps-rounded-full aps-flex aps-items-center aps-justify-center aps-shadow-md hover:aps-scale-110 aps-transition-transform aps-z-10">
                                <i data-lucide="bookmark" class="aps-w-4 aps-h-4 aps-text-gray-500"></i>
                            </button>
                            <span class="aps-drop-shadow-md">Preview</span>
                        </div>

                        <div class="aps-p-5">
                            <div class="aps-flex aps-justify-between aps-items-start aps-mb-3">
                                <h3 class="aps-text-lg aps-font-bold aps-text-gray-900">Dummy Product 1</h3>
                                <div class="aps-text-right">
                                    <span class="aps-block aps-text-xs aps-text-gray-400 aps-line-through">$19.99/mo</span>
                                    <div class="aps-flex aps-items-baseline aps-justify-end">
                                        <span class="aps-text-xl aps-font-bold aps-text-gray-900">$9.99</span>
                                        <span class="aps-text-xs aps-text-gray-500 aps-ml-0.5">/mo</span>
                                    </div>
                                    <span class="aps-inline-block aps-mt-1 aps-px-2.5 aps-py-1 aps-bg-emerald-100 aps-text-emerald-600 aps-text-[10px] aps-font-bold aps-rounded-full">50% OFF</span>
                                </div>
                            </div>

                            <p class="aps-text-sm aps-text-gray-600 aps-mb-3 aps-leading-relaxed">
                                A compact demo tool with solid performance and easy onboarding — great for testing layouts and pagination behavior.
                            </p>

                            <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-sm aps-text-gray-600 aps-mb-4">
                                <i data-lucide="star" class="aps-w-3.5 aps-h-3.5 aps-text-amber-400 aps-fill-current"></i>
                                <span>Featured</span>
                            </div>

                            <div class="aps-flex aps-flex-wrap aps-gap-3 aps-mb-5">
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Easy Setup</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Basic Analytics</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="check" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500"></i>
                                    <span>Responsive</span>
                                </div>
                                <div class="aps-flex aps-items-center aps-gap-1.5 aps-text-xs aps-text-gray-600">
                                    <i data-lucide="zap" class="aps-w-3.5 aps-h-3.5 aps-text-emerald-500 aps-fill-current"></i>
                                    <span>Fast</span>
                                </div>
                            </div>

                            <div class="aps-pt-4 aps-border-t aps-border-gray-100">
                                <div class="aps-flex aps-items-center aps-justify-between aps-mb-4">
                                    <div class="aps-flex aps-items-center aps-gap-3">
                                        <div class="aps-flex aps-items-center">
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-amber-400 aps-fill-current"></i>
                                            <i data-lucide="star" class="aps-w-4 aps-h-4 aps-text-gray-200"></i>
                                            <span class="aps-ml-1.5 aps-text-sm aps-font-bold aps-text-gray-900">4.2/5</span>
                                        </div>
                                        <span class="aps-text-xs aps-text-gray-400">128 reviews</span>
                                    </div>
                                    <div class="aps-flex aps-items-center aps-gap-1 aps-px-2.5 aps-py-1 aps-bg-emerald-50 aps-text-emerald-600 aps-rounded-full aps-text-[11px] aps-font-semibold">
                                        <i data-lucide="users" class="aps-w-3 aps-h-3"></i>
                                        <span>1K+ users</span>
                                    </div>
                                </div>

                                <button class="aps-w-full aps-py-3.5 aps-bg-gray-900 aps-text-white aps-rounded-xl aps-text-sm aps-font-semibold hover:aps-bg-gray-800 aps-transition-colors aps-flex aps-items-center aps-justify-center aps-gap-2 group">
                                    Visit Site
                                    <i data-lucide="external-link" class="aps-w-4 aps-h-4 group-hover:aps-translate-x-0.5 group-hover:-aps-translate-y-0.5 aps-transition-transform"></i>
                                </button>
                                <p class="aps-text-center aps-mt-3 aps-text-xs aps-text-gray-400">14-day free trial</p>
                            </div>
                        </div>
                    </article>

                </div>
            </main>
        </div>
    </div>
</div>
<!-- End Affiliate Product Showcase -->
