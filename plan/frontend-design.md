<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f9fafb;
            color: #111827;
            line-height: 1.5;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        .main-layout {
            display: flex;
            gap: 32px;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            flex-shrink: 0;
        }

        .search-box {
            position: relative;
            margin-bottom: 28px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            background-color: #fff;
            outline: none;
        }

        .search-box::before {
            content: "";
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .filter-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .clear-all {
            font-size: 13px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .filter-section {
            margin-bottom: 24px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: block;
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tab {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            background-color: #f3f4f6;
            color: #4b5563;
            transition: all 0.2s;
        }

        .tab.active {
            background-color: #3b82f6;
            color: #fff;
            border-color: #3b82f6;
        }

        .tags-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            background-color: #fff;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .tag:hover {
            background-color: #f9fafb;
        }

        .tag.active {
            background-color: #eff6ff;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        /* Main Content */
        .main-content {
            flex: 1;
        }

        .content-header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .sort-dropdown {
            position: relative;
        }

        .sort-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            min-width: 160px;
            justify-content: space-between;
        }

        .sort-label {
            color: #9ca3af;
            font-weight: 400;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        /* Card Styles */
        .tool-card {
            background-color: #fff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            position: relative;
            transition: box-shadow 0.2s;
        }

        .tool-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .featured-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background-color: #6366f1;
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .featured-badge::before {
            content: "★";
            font-size: 10px;
        }

        .view-count {
            position: absolute;
            top: 12px;
            right: 12px;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #f59e0b;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .view-count::before {
            content: "";
            width: 12px;
            height: 12px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%23f59e0b'%3E%3Cpath d='M10 12a2 2 0 100-4 2 2 0 000 4z'/%3E%3Cpath fill-rule='evenodd' d='M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }

        .card-image {
            height: 160px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
        }

        .bookmark-icon {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 36px;
            height: 36px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .bookmark-icon::after {
            content: "";
            width: 16px;
            height: 16px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }

        .card-image.pink {
            background: linear-gradient(135deg, #f9a8d4 0%, #f472b6 50%, #ec4899 100%);
        }

        .card-image.cyan {
            background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 50%, #06b6d4 100%);
        }

        .card-image.purple {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 50%, #8b5cf6 100%);
        }

        .card-body {
            padding: 20px;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .tool-name {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tool-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .tool-icon.orange {
            background-color: #ffedd5;
            color: #ea580c;
        }

        .price-block {
            text-align: right;
        }

        .original-price {
            font-size: 12px;
            color: #9ca3af;
            text-decoration: line-through;
            display: block;
        }

        .current-price {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: baseline;
        }

        .price-period {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-left: 2px;
        }

        .discount-badge {
            display: inline-block;
            background-color: #d1fae5;
            color: #10b981;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            margin-top: 6px;
        }

        .tool-description {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .inline-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 16px;
        }

        .inline-tag::before {
            content: "★";
            color: #fbbf24;
            font-size: 12px;
        }

        .features-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #4b5563;
        }

        .feature-item::before {
            content: "";
            width: 14px;
            height: 14px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2310b981'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }

        .feature-item.dimmed {
            color: #9ca3af;
        }

        .feature-item.dimmed::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z' clip-rule='evenodd'/%3E%3C/svg%3E");
        }

        .feature-item.bolt::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2310b981'%3E%3Cpath fill-rule='evenodd' d='M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z' clip-rule='evenodd'/%3E%3C/svg%3E");
        }

        .card-footer {
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }

        .stats-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .stats-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .rating-stars {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .star {
            color: #fbbf24;
            font-size: 14px;
        }

        .star.empty {
            color: #e5e7eb;
        }

        .rating-text {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-left: 6px;
        }

        .reviews-count {
            font-size: 13px;
            color: #9ca3af;
        }

        .users-pill {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
        }

        .users-pill.green {
            color: #059669;
            background-color: #ecfdf5;
        }

        .users-pill.red {
            color: #dc2626;
            background-color: #fef2f2;
        }

        .users-pill::before {
            content: "";
            width: 12px;
            height: 12px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'%3E%3Cpath d='M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }

        .action-button {
            width: 100%;
            padding: 14px 24px;
            background-color: #111827;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .action-button:hover {
            background-color: #1f2937;
        }

        .action-button::after {
            content: "";
            width: 14px;
            height: 14px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }

        .trial-text {
            text-align: center;
            margin-top: 12px;
            font-size: 12px;
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .category-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 8px;
            }

            .tags-grid {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 8px;
            }
        }

        @media (max-width: 640px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px 16px;
            }
        }

        /* Custom scrollbar for filters */
        .category-tabs::-webkit-scrollbar,
        .tags-grid::-webkit-scrollbar {
            height: 4px;
        }

        .category-tabs::-webkit-scrollbar-track,
        .tags-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .category-tabs::-webkit-scrollbar-thumb,
        .tags-grid::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }
    </style>
<base target="_blank">
</head>
<body>
    <div class="container">
        <div class="main-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="search-box">
                    <input type="text" placeholder="Search tools...">
                </div>

                <div class="filter-header">
                    <span class="filter-title">Filter Tools</span>
                    <a href="#" class="clear-all">Clear All</a>
                </div>

                <div class="filter-section">
                    <span class="section-label">Category</span>
                    <div class="category-tabs">
                        <div class="tab active">All Tools</div>
                        <div class="tab">Hosting</div>
                        <div class="tab">AI Tools</div>
                        <div class="tab">SEO Tools</div>
                        <div class="tab">Marketing Tools</div>
                    </div>
                </div>

                <div class="filter-section">
                    <span class="section-label">Tags</span>
                    <div class="tags-grid">
                        <div class="tag active">⭐ Featured</div>
                        <div class="tag">✍️ Writing</div>
                        <div class="tag">🎥 Video</div>
                        <div class="tag">🎤 Audio</div>
                        <div class="tag">🎨 Design</div>
                        <div class="tag">🆓 Free Trial</div>
                        <div class="tag">💳 No CC</div>
                        <div class="tag">🎁 Free Forever</div>
                        <div class="tag active">✅ Verified</div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <div class="content-header">
                    <div class="sort-dropdown">
                        <button class="sort-btn">
                            <span class="sort-label">Sort by</span>
                            <span>Featured</span>
                            <span>▼</span>
                        </button>
                    </div>
                </div>

                <div class="cards-grid">
                    <!-- Card 1 -->
                    <article class="tool-card">
                        <div class="featured-badge">Featured</div>
                        <div class="card-image pink">
                            <div class="bookmark-icon"></div>
                            Preview
                        </div>
                        <div class="card-body">
                            <div class="card-header-row">
                                <h3 class="tool-name">New Featured Tool</h3>
                                <div class="price-block">
                                    <span class="original-price">$39.99/mo</span>
                                    <div class="current-price">
                                        $19.99<span class="price-period">/mo</span>
                                    </div>
                                    <span class="discount-badge">50% OFF</span>
                                </div>
                            </div>
                            
                            <p class="tool-description">
                                Latest release with modern UX and improved analytics — featured to showcase new capabilities.
                            </p>

                            <div class="inline-tag">Featured</div>

                            <div class="features-list">
                                <div class="feature-item">Realtime Analytics</div>
                                <div class="feature-item">AI Suggestions</div>
                            </div>

                            <div class="card-footer">
                                <div class="stats-row">
                                    <div class="stats-left">
                                        <div class="rating-stars">
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="rating-text">4.9/5</span>
                                        </div>
                                        <span class="reviews-count">1,024 reviews</span>
                                    </div>
                                    <div class="users-pill green">1K+ users</div>
                                </div>

                                <button class="action-button">Explore Now</button>
                            </div>
                        </div>
                    </article>

                    <!-- Card 2 -->
                    <article class="tool-card">
                        <div class="featured-badge">Featured</div>
                        <div class="view-count">412 viewed</div>
                        <div class="card-image cyan">
                            <div class="bookmark-icon"></div>
                            Product Dashboard Preview
                        </div>
                        <div class="card-body">
                            <div class="card-header-row">
                                <h3 class="tool-name">
                                    <span class="tool-icon orange">📤</span>
                                    SEMrush Pro
                                </h3>
                                <div class="price-block">
                                    <span class="original-price">$229.95/mo</span>
                                    <div class="current-price">
                                        $119<span class="price-period">/mo</span>
                                    </div>
                                    <span class="discount-badge">48% OFF</span>
                                </div>
                            </div>
                            
                            <p class="tool-description">
                                The most accurate difficulty score in the industry. Find low-competition keywords and spy on competitors' traffic sources easily.
                            </p>

                            <div class="inline-tag">Featured</div>

                            <div class="features-list">
                                <div class="feature-item">Keyword Research</div>
                                <div class="feature-item">Competitor Analysis</div>
                                <div class="feature-item">Site Audit</div>
                                <div class="feature-item dimmed">Traffic Analytics</div>
                            </div>

                            <div class="card-footer">
                                <div class="stats-row">
                                    <div class="stats-left">
                                        <div class="rating-stars">
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="rating-text">5.0/5</span>
                                        </div>
                                        <span class="reviews-count">3,421 reviews</span>
                                    </div>
                                    <div class="users-pill red">10M+ users</div>
                                </div>

                                <button class="action-button">Claim Discount</button>
                                <div class="trial-text">14-day free trial available</div>
                            </div>
                        </div>
                    </article>

                    <!-- Card 3 -->
                    <article class="tool-card">
                        <div class="featured-badge">Featured</div>
                        <div class="view-count">128 viewed</div>
                        <div class="card-image purple">
                            <div class="bookmark-icon"></div>
                            Preview
                        </div>
                        <div class="card-body">
                            <div class="card-header-row">
                                <h3 class="tool-name">Dummy Product 1</h3>
                                <div class="price-block">
                                    <span class="original-price">$19.99/mo</span>
                                    <div class="current-price">
                                        $9.99<span class="price-period">/mo</span>
                                    </div>
                                    <span class="discount-badge">50% OFF</span>
                                </div>
                            </div>
                            
                            <p class="tool-description">
                                A compact demo tool with solid performance and easy onboarding — great for testing layouts and pagination behavior.
                            </p>

                            <div class="inline-tag">Featured</div>

                            <div class="features-list">
                                <div class="feature-item">Easy Setup</div>
                                <div class="feature-item">Basic Analytics</div>
                                <div class="feature-item">Responsive</div>
                                <div class="feature-item bolt">Fast</div>
                            </div>

                            <div class="card-footer">
                                <div class="stats-row">
                                    <div class="stats-left">
                                        <div class="rating-stars">
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star">★</span>
                                            <span class="star empty">★</span>
                                            <span class="rating-text">4.2/5</span>
                                        </div>
                                        <span class="reviews-count">128 reviews</span>
                                    </div>
                                    <div class="users-pill green">1K+ users</div>
                                </div>

                                <button class="action-button">Visit Site</button>
                                <div class="trial-text">14-day free trial</div>
                            </div>
                        </div>
                    </article>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Tab interaction
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Tag interaction
        document.querySelectorAll('.tag').forEach(tag => {
            tag.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });

        // Clear all functionality
        document.querySelector('.clear-all').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tag').forEach(t => t.classList.remove('active'));
            document.querySelector('.tab').classList.add('active');
        });
    </script>
</body>
</html>