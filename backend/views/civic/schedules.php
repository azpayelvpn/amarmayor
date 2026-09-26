<div class="container py-4">
    <!-- Page Banner -->
    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 p-md-5 mb-4 position-relative overflow-hidden">
        <div class="position-relative" style="z-index: 2;">
            <span class="badge bg-light text-primary fw-bold px-3 py-1 rounded-pill mb-2">ময়মনসিংহ সিটি কর্পোরেশন</span>
            <h2 class="fw-bold mb-2">নাগরিক সেবা সনদ ও নিয়মিত সময়সূচি</h2>
            <p class="mb-0 text-white-50" style="max-width: 700px;">
                পরিচ্ছন্ন ও সুন্দর ময়মনসিংহ গড়ার লক্ষ্যে দৈনন্দিন বর্জ্য সংগ্রহ, মশক নিধন কার্যক্রম এবং নাগরিক সেবা সমাধানের সময়সীমার পূর্ণ বিবরণ।
            </p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-fill bg-white p-2 rounded-4 shadow-sm border mb-4" id="scheduleTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold py-3" id="charter-tab" data-bs-toggle="tab" data-bs-target="#charter" type="button" role="tab">
                <i class="bi bi-award-fill me-1 text-primary"></i> নাগরিক সেবা সনদ (SLA)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3" id="waste-tab" data-bs-toggle="tab" data-bs-target="#waste" type="button" role="tab">
                <i class="bi bi-trash-fill me-1 text-success"></i> বর্জ্য অপসারণ সময়সূচি
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3" id="mosquito-tab" data-bs-toggle="tab" data-bs-target="#mosquito" type="button" role="tab">
                <i class="bi bi-shield-shaded me-1 text-warning"></i> মশক নিধন রুটিন
            </button>
        </li>
    </ul>

    <div class="tab-content" id="scheduleTabContent">
        <!-- 1. CITIZEN CHARTER / SLA -->
        <div class="tab-pane fade show active" id="charter" role="tabpanel">
            <div class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-check2-circle text-primary me-2"></i>নাগরিক সেবা সনদ ও সমাধানের নির্ধারিত সময়সীমা
                </h4>
                <p class="text-muted small mb-4">অভিযোগ দাখিলের পর সিটি কর্পোরেশনের সংশ্লিষ্ট বিভাগ কর্তৃক সমাধানের প্রতিশ্রুতিবদ্ধ সর্বোচ্চ সময়সীমা (SLA):</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">সেবার খাত ও ধরন</th>
                                <th>দায়িত্বপ্রাপ্ত বিভাগ</th>
                                <th>সমাধানের সময়সীমা</th>
                                <th>অগ্রাধিকার মাত্রা</th>
                                <th class="text-end pe-3">জরুরি ব্যবস্থা</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark d-block">🚨 জরুরি নাগরিক বিপত্তি</strong>
                                    <small class="text-muted">খোলা ম্যানহোল, ঝুলন্ত বিদ্যুৎ তার, মৃত পশু, রাস্তায় বিপজ্জনক ধস</small>
                                </td>
                                <td><span class="badge bg-danger-subtle text-danger">কন্ট্রোল রুম ও কুইক রেসপন্স</span></td>
                                <td><strong class="text-danger fs-6">২ হতে ৪ ঘণ্টা</strong></td>
                                <td><span class="badge bg-danger">P1 জরুরি</span></td>
                                <td class="text-end pe-3">তাৎক্ষণিক ব্যারিকেড ও প্রতিকার</td>
                            </tr>
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark d-block">কঠিন বর্জ্য ও ডাস্টবিন উপচে পড়া</strong>
                                    <small class="text-muted">মহল্লার আবর্জনা, ডাস্টবিনের ময়লা ও দুর্গন্ধ</small>
                                </td>
                                <td><span class="badge bg-success-subtle text-success">বর্জ্য ব্যবস্থাপনা বিভাগ</span></td>
                                <td><strong class="text-success fs-6">২৪ ঘণ্টা</strong></td>
                                <td><span class="badge bg-warning text-dark">P2 স্বাভাবিক</span></td>
                                <td class="text-end pe-3">দৈনিক পরিচ্ছন্নতা ক্রু</td>
                            </tr>
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark d-block">ড্রেনেজ ও জলবদ্ধতা</strong>
                                    <small class="text-muted">ড্রেন ভরাট হওয়া, পানি নিষ্কাশনে বাধা, স্ল্যাব ভাঙা</small>
                                </td>
                                <td><span class="badge bg-info-subtle text-info-emphasis">বর্জ্য ও প্রকৌশল বিভাগ</span></td>
                                <td><strong class="text-dark fs-6">২৪ হতে ৪৮ ঘণ্টা</strong></td>
                                <td><span class="badge bg-warning text-dark">P2 স্বাভাবিক</span></td>
                                <td class="text-end pe-3">ডি-সিল্টিং ও স্ল্যাব মেরামত</td>
                            </tr>
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark d-block">সড়ক বাতি সমস্যা</strong>
                                    <small class="text-muted">বাতি না জ্বলা, সুইচ অকেজো, অন্ধকার গলি</small>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary">বিদ্যুৎ ও যান্ত্রিক বিভাগ</span></td>
                                <td><strong class="text-dark fs-6">৪৮ ঘণ্টা</strong></td>
                                <td><span class="badge bg-info text-dark">P3 সাধারণ</span></td>
                                <td class="text-end pe-3">ল্যাডার ভ্যান ও ইলেকট্রিশিয়ান</td>
                            </tr>
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark d-block">রাস্তার ছোটখাটো গর্ত ও খানাখন্দ</strong>
                                    <small class="text-muted">যানবাহন চলাচলে বাধা সৃষ্টিকারী প্যাচওয়ার্ক</small>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary">প্রকৌশল বিভাগ</span></td>
                                <td><strong class="text-dark fs-6">৭২ ঘণ্টা</strong></td>
                                <td><span class="badge bg-info text-dark">P3 সাধারণ</span></td>
                                <td class="text-end pe-3">রাস্তা সংস্কার টিম</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. WASTE COLLECTION SCHEDULE -->
        <div class="tab-pane fade" id="waste" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-sunrise-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">প্রাথমিক বর্জ্য সংগ্রহ (বাড়ি বাড়ি)</h5>
                                <small class="text-muted">ভ্যান ও রিকশার মাধ্যমে কালেকশন</small>
                            </div>
                        </div>
                        <h4 class="text-success fw-bold">সকাল ০৬:০০ — সকাল ১০:০০</h4>
                        <p class="small text-muted mb-3">
                            নগরবাসীর প্রতি অনুরোধ, সকাল ১০:০০ ঘটিকার পূর্বেই গৃহস্থালির বর্জ্য নির্ধারিত ভ্যানে অথবা নির্দিষ্ট বিন/পাত্রে রাখুন।
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-2"><i class="bi bi-check2 text-success me-1"></i> <strong>আওতাভুক্ত এলাকা:</strong> ১ হতে ৩৩ নং সকল ওয়ার্ডের পাড়া ও মহল্লা।</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success me-1"></i> <strong>দায়িত্বপ্রাপ্ত:</strong> ওয়ার্ড পরিচ্ছন্নতা কর্মী ও সুপারভাইজার।</li>
                            <li><i class="bi bi-check2 text-success me-1"></i> <strong>প্রতিদিন:</strong> সপ্তাহে ৭ দিন নিয়মিত সেবা চলমান।</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-primary text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">মাধ্যমিক অপসারণ (এসটিএস হতে ডাম্পিং সাইট)</h5>
                                <small class="text-muted">কম্প্যাক্টর ট্রাক ও ডাম্পার পরিবহন</small>
                            </div>
                        </div>
                        <h4 class="text-primary fw-bold">সন্ধ্যা ০৭:০০ — রাত ১১:০০</h4>
                        <p class="small text-muted mb-3">
                            যানজট এড়াতে ও পরিবেশের সুরক্ষায় রাত্রিকালীন সময়ে ভারী যানবাহনের মাধ্যমে মাধ্যমিক ডাম্পিং স্টেশন হতে ময়লা অপসারণ করা হয়।
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-1"></i> <strong>স্থান:</strong> সকল সেকেন্ডারি ট্রান্সফার স্টেশন (STS) ও বড় ডাস্টবিন পয়েন্ট।</li>
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-1"></i> <strong>গন্তব্য:</strong> সিটি কর্পোরেশনের কেন্দ্রীয় ল্যান্ডফিল সাইট।</li>
                            <li><i class="bi bi-check2 text-primary me-1"></i> <strong>যানবাহন:</strong> হাইড্রোলিক কম্প্যাক্টর ও কাভার্ড ডাম্প ট্রাক।</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. MOSQUITO CONTROL ROUTINE -->
        <div class="tab-pane fade" id="mosquito" role="tabpanel">
            <div class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-shield-check text-warning me-2"></i>মশক নিয়ন্ত্রণ ও স্প্রে কার্যক্রমের সময়সূচি
                </h4>
                <p class="text-muted small mb-4">মশার বংশবিস্তার রোধে প্রতিদিন দুই পালায় ওয়ার্ডভিত্তিক লার্ভিসাইড ও ফগিং অপারেশন পরিচালিত হয়:</p>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-droplet-half text-info fs-4"></i>
                                <h6 class="fw-bold mb-0 text-dark">১ম পালা: লার্ভিসাইড স্প্রে (ড্রেন ও বদ্ধ জলাশয়)</h6>
                            </div>
                            <strong class="text-info fs-5 d-block mb-2">সকাল ০৭:০০ — সকাল ০৯:৩০</strong>
                            <p class="small text-muted mb-0">ড্রেন, ডোবা, নালা ও বদ্ধ পানিতে মশার লার্ভা ধ্বংসকারী ওষুধ ছিটানো হয়।</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-cloud-fog2-fill text-warning fs-4"></i>
                                <h6 class="fw-bold mb-0 text-dark">২য় পালা: ফগিং অপারেশন (উড়ন্ত মশক নিধন)</h6>
                            </div>
                            <strong class="text-warning-emphasis fs-5 d-block mb-2">বিকাল ০৪:৩০ — সন্ধ্যা ০৬:৩০</strong>
                            <p class="small text-muted mb-0">ফগার মেশিনের মাধ্যমে ধোঁয়া প্রয়োগ করে উড়ন্ত মশা নিধন করা হয়।</p>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3">সাপ্তাহিক অঞ্চলভিত্তিক রুটিন:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>বার</th>
                                <th>অঞ্চল ১ (Zone 1)</th>
                                <th>অঞ্চল ২ (Zone 2)</th>
                                <th>অঞ্চল ৩ (Zone 3)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>শনিবার</td><td>ওয়ার্ড ১, ২, ৪</td><td>ওয়ার্ড ৩, ৫, ৭</td><td>ওয়ার্ড ৮, ৯, ১০</td></tr>
                            <tr><td>রবিবার</td><td>ওয়ার্ড ১১, ১২, ২৭</td><td>ওয়ার্ড ১৩, ১৪, ১৫</td><td>ওয়ার্ড ১৬, ১৭, ১৮</td></tr>
                            <tr><td>সোমবার</td><td>ওয়ার্ড ২৮, ২৯, ৩০</td><td>ওয়ার্ড ১৯, ২০, ২১</td><td>ওয়ার্ড ২২, ২৩, ২৪</td></tr>
                            <tr><td>মঙ্গলবার</td><td>ওয়ার্ড ১, ২, ৪ (পুনঃস্প্রে)</td><td>ওয়ার্ড ২৫, ২৬, ৩১</td><td>ওয়ার্ড ৩২, ৩৩</td></tr>
                            <tr><td>বুধবার</td><td>ওয়ার্ড ১১, ১২, ২৭ (পুনঃস্প্রে)</td><td>ওয়ার্ড ৩, ৫, ৭ (পুনঃস্প্রে)</td><td>ওয়ার্ড ৮, ৯, ১০ (পুনঃস্প্রে)</td></tr>
                            <tr><td>বৃহস্পতিবার</td><td>ওয়ার্ড ২৮, ২৯, ৩০ (পুনঃস্প্রে)</td><td>ওয়ার্ড ১৩, ১৪, ১৫ (পুনঃস্প্রে)</td><td>ওয়ার্ড ১৬, ১৭, ১৮ (পুনঃস্প্রে)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
