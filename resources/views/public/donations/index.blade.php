@extends('layouts.public')

@section('title', __('donations.meta.title'))
@section('meta_description', __('donations.meta.description'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-4">
                    <h2 class="h3 mb-0 text-center">
                        <i class="fas fa-heart me-2"></i>{{ __('donations.title') }}
                    </h2>
                    <p class="text-center mb-0 mt-2 opacity-75">{{ __('donations.subtitle') }}</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form id="donationForm" method="POST" action="{{ route('donations.process') }}">
                        @csrf

                        {{-- Donor Information --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2 text-primary"></i>{{ __('donations.form.personal_info') }}
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">{{ __('donations.form.full_name') }} *</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                           id="full_name" name="full_name" required
                                           value="{{ old('full_name') }}"
                                           placeholder="{{ __('donations.form.full_name_placeholder') }}">
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">{{ __('donations.form.email') }} *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" required
                                           value="{{ old('email') }}"
                                           placeholder="{{ __('donations.form.email_placeholder') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">{{ __('donations.form.phone') }}</label>
                                <div class="input-group">
                                    <div class="phone-country-wrapper position-relative" style="min-width: 180px;">
                                        <button type="button" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between"
                                                id="phoneCountryBtn" onclick="toggleCountryDropdown()">
                                            <span id="phoneCountryDisplay">�� Sénégal +221</span>
                                            <i class="fas fa-chevron-down ms-2" style="font-size: 0.7rem;"></i>
                                        </button>
                                        <input type="hidden" id="phone_country" name="phone_country" value="{{ old('phone_country', '+221') }}">
                                        <div class="phone-country-dropdown" id="phoneCountryDropdown">
                                            <div class="phone-country-search-box">
                                                <input type="text" class="form-control form-control-sm" id="phoneCountrySearch"
                                                       placeholder="Rechercher un pays..." autocomplete="off">
                                            </div>
                                            <div class="phone-country-list" id="phoneCountryList"></div>
                                        </div>
                                    </div>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="Votre numéro">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_anonymous">
                                    {{ __('donations.form.anonymous') }}
                                </label>
                            </div>
                        </div>

                        {{-- Donation Type --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>{{ __('donations.form.donation_type') }}
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="donation_type"
                                               id="type_single" value="single" checked
                                               onchange="toggleFrequency(false)">
                                        <label class="form-check-label" for="type_single">
                                            {{ __('donations.form.one_time') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="donation_type"
                                               id="type_monthly" value="monthly"
                                               onchange="toggleFrequency(true)">
                                        <label class="form-check-label" for="type_monthly">
                                            {{ __('donations.form.monthly') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Frequency (hidden by default) --}}
                            <div id="frequencySection" class="mb-3" style="display: none;">
                                <label for="frequency" class="form-label">{{ __('donations.form.frequency') }}</label>
                                <select class="form-select" id="frequency" name="frequency">
                                    <option value="monthly">{{ __('donations.frequency.monthly') }}</option>
                                    <option value="quarterly">{{ __('donations.frequency.quarterly') }}</option>
                                    <option value="yearly">{{ __('donations.frequency.yearly') }}</option>
                                </select>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-money-bill-wave me-2 text-primary"></i>{{ __('donations.form.amount') }}
                            </h5>

                            {{-- Suggested Amounts --}}
                            <div class="row g-2 mb-3">
                                @foreach($suggestedAmounts as $amount)
                                    <div class="col-4 col-md-3">
                                        <button type="button" class="btn btn-outline-primary w-100 amount-btn"
                                                data-amount="{{ $amount }}"
                                                onclick="selectAmount({{ $amount }})">
                                            {{ number_format($amount, 0, ',', ' ') }} FCFA
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">{{ __('donations.form.custom_amount') }} *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" required min="500" max="10000000"
                                           value="{{ old('amount', 5000) }}">
                                    <span class="input-group-text">FCFA</span>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Min : 500 FCFA — Max : 10 000 000 FCFA</small>
                            </div>
                        </div>

                        {{-- Payment Provider Selection --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-credit-card me-2 text-primary"></i>{{ __('donations.form.payment_method') }}
                            </h5>

                            {{-- Provider Tabs --}}
                            <ul class="nav nav-pills mb-3" id="paymentProviderTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="bictorys-tab" data-bs-toggle="pill"
                                            data-bs-target="#bictorys-methods" type="button" role="tab"
                                            onclick="selectProvider('bictorys')">
                                        <i class="fas fa-bolt me-1"></i> Bictorys
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="paypal-tab" data-bs-toggle="pill"
                                            data-bs-target="#paypal-methods" type="button" role="tab"
                                            onclick="selectProvider('paypal')">
                                        <i class="fab fa-paypal me-1"></i> PayPal / Carte internationale
                                    </button>
                                </li>
                            </ul>

                            <input type="hidden" id="payment_provider" name="payment_provider" value="bictorys">

                            <div class="tab-content" id="paymentProviderContent">
                                {{-- Bictorys Methods --}}
                                <div class="tab-pane fade show active" id="bictorys-methods" role="tabpanel">
                                    <div class="row g-3">
                                        @foreach($bictorysMethods as $key => $method)
                                            <div class="col-md-4">
                                                <div class="payment-method-card form-check card h-100">
                                                    <input class="form-check-input" type="radio"
                                                           name="payment_method" id="method_{{ $key }}"
                                                           value="{{ $key }}" {{ $loop->first ? 'checked' : '' }}>
                                                    <label class="form-check-label card-body text-center" for="method_{{ $key }}">
                                                        <i class="{{ $method['icon'] }} fa-2x mb-2" style="color: {{ $method['color'] }}"></i>
                                                        <div class="fw-bold">{{ $method['name'] }}</div>
                                                        <small class="text-muted d-block">{{ $method['description'] }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Paiement mobile direct via Bictorys (Orange Money, Wave, carte).
                                    </div>
                                </div>

                                {{-- PayPal Methods --}}
                                <div class="tab-pane fade" id="paypal-methods" role="tabpanel">
                                    <div class="row g-3">
                                        @foreach($paypalMethods as $key => $method)
                                            <div class="col-md-6">
                                                <div class="payment-method-card form-check card h-100">
                                                    <input class="form-check-input" type="radio"
                                                           name="payment_method" id="paypal_{{ $key }}"
                                                           value="{{ $key }}">
                                                    <label class="form-check-label card-body text-center" for="paypal_{{ $key }}">
                                                        <i class="{{ $method['icon'] }} fa-2x mb-2" style="color: {{ $method['color'] }}"></i>
                                                        <div class="fw-bold">{{ $method['name'] }}</div>
                                                        <small class="text-muted d-block">{{ $method['description'] }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Le montant sera converti en USD (~<span id="usdAmount">0</span> $) pour le traitement PayPal.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Message --}}
                        <div class="mb-4">
                            <label for="message" class="form-label">{{ __('donations.form.message') }}</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" name="message" rows="3"
                                      placeholder="{{ __('donations.form.message_placeholder') }}">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-heart me-2"></i>{{ __('donations.form.submit') }}
                            </button>
                        </div>

                        {{-- Security Notice --}}
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                {{ __('donations.form.secure_payment') }}
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading Modal --}}
<div class="modal fade" id="processingModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>{{ __('donations.processing.title') }}</h5>
                <p class="text-muted mb-0">{{ __('donations.processing.message') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.payment-method-card {
    cursor: pointer;
    transition: all 0.2s;
}
.payment-method-card:hover {
    border-color: var(--bs-primary);
    transform: translateY(-2px);
}
.payment-method-card .form-check-input {
    position: absolute;
    opacity: 0;
}
.payment-method-card .form-check-input:checked + .form-check-label {
    background-color: var(--bs-primary-bg-subtle);
    border-color: var(--bs-primary);
}
.amount-btn.active {
    background-color: var(--bs-primary);
    color: white;
}
/* Phone country selector */
.phone-country-wrapper { z-index: 10; }
.phone-country-dropdown {
    display: none; position: absolute; top: 100%; left: 0; right: 0;
    background: #fff; border: 1px solid #dee2e6; border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1050;
    margin-top: 4px; max-height: 300px; overflow: hidden;
}
.phone-country-dropdown.show { display: block; }
.phone-country-search-box { padding: 8px; border-bottom: 1px solid #e9ecef; }
.phone-country-list { max-height: 240px; overflow-y: auto; }
.phone-country-item {
    padding: 8px 12px; cursor: pointer; display: flex; align-items: center;
    gap: 8px; font-size: 0.9rem; transition: background 0.15s;
}
.phone-country-item:hover { background: #f0f9f4; }
.phone-country-item .country-flag { font-size: 1.2rem; }
.phone-country-item .country-name { flex: 1; color: #374151; }
.phone-country-item .country-code { color: #6b7280; font-weight: 600; }
#phoneCountryBtn { font-size: 0.85rem; height: 100%; border-radius: 0.375rem 0 0 0.375rem; }
</style>
@endpush

@push('scripts')
<script>
// === Phone Country Selector with Search ===
const phoneCountries = [
    { code: '+221', flag: '🇸🇳', name: 'Sénégal' },
    { code: '+223', flag: '🇲🇱', name: 'Mali' },
    { code: '+224', flag: '🇬🇳', name: 'Guinée' },
    { code: '+225', flag: '🇨🇮', name: 'Côte d\'Ivoire' },
    { code: '+226', flag: '🇧🇫', name: 'Burkina Faso' },
    { code: '+227', flag: '🇳🇪', name: 'Niger' },
    { code: '+228', flag: '🇹🇬', name: 'Togo' },
    { code: '+229', flag: '🇧🇯', name: 'Bénin' },
    { code: '+230', flag: '🇲🇺', name: 'Maurice' },
    { code: '+231', flag: '🇱🇷', name: 'Liberia' },
    { code: '+232', flag: '🇸🇱', name: 'Sierra Leone' },
    { code: '+233', flag: '🇬🇭', name: 'Ghana' },
    { code: '+234', flag: '🇳🇬', name: 'Nigeria' },
    { code: '+235', flag: '🇹🇩', name: 'Tchad' },
    { code: '+236', flag: '🇨🇫', name: 'Centrafrique' },
    { code: '+237', flag: '🇨🇲', name: 'Cameroun' },
    { code: '+238', flag: '🇨🇻', name: 'Cap-Vert' },
    { code: '+240', flag: '🇬🇶', name: 'Guinée équatoriale' },
    { code: '+241', flag: '🇬🇦', name: 'Gabon' },
    { code: '+242', flag: '🇨🇬', name: 'Congo' },
    { code: '+243', flag: '🇨🇩', name: 'RD Congo' },
    { code: '+244', flag: '🇦🇴', name: 'Angola' },
    { code: '+245', flag: '🇬🇼', name: 'Guinée-Bissau' },
    { code: '+248', flag: '🇸🇨', name: 'Seychelles' },
    { code: '+250', flag: '🇷🇼', name: 'Rwanda' },
    { code: '+251', flag: '🇪🇹', name: 'Éthiopie' },
    { code: '+252', flag: '🇸🇴', name: 'Somalie' },
    { code: '+253', flag: '🇩🇯', name: 'Djibouti' },
    { code: '+254', flag: '🇰🇪', name: 'Kenya' },
    { code: '+255', flag: '🇹🇿', name: 'Tanzanie' },
    { code: '+256', flag: '🇺🇬', name: 'Ouganda' },
    { code: '+257', flag: '🇧🇮', name: 'Burundi' },
    { code: '+258', flag: '🇲🇿', name: 'Mozambique' },
    { code: '+260', flag: '🇿🇲', name: 'Zambie' },
    { code: '+261', flag: '🇲🇬', name: 'Madagascar' },
    { code: '+262', flag: '🇷🇪', name: 'Réunion' },
    { code: '+263', flag: '🇿🇼', name: 'Zimbabwe' },
    { code: '+265', flag: '🇲🇼', name: 'Malawi' },
    { code: '+212', flag: '🇲🇦', name: 'Maroc' },
    { code: '+213', flag: '🇩🇿', name: 'Algérie' },
    { code: '+216', flag: '🇹🇳', name: 'Tunisie' },
    { code: '+218', flag: '🇱🇾', name: 'Libye' },
    { code: '+220', flag: '🇬🇲', name: 'Gambie' },
    { code: '+269', flag: '🇰🇲', name: 'Comores' },
    { code: '+27', flag: '🇿🇦', name: 'Afrique du Sud' },
    { code: '+1', flag: '🇺🇸', name: 'États-Unis' },
    { code: '+1', flag: '🇨🇦', name: 'Canada' },
    { code: '+33', flag: '🇫🇷', name: 'France' },
    { code: '+32', flag: '🇧🇪', name: 'Belgique' },
    { code: '+41', flag: '🇨🇭', name: 'Suisse' },
    { code: '+44', flag: '🇬🇧', name: 'Royaume-Uni' },
    { code: '+49', flag: '🇩🇪', name: 'Allemagne' },
    { code: '+34', flag: '🇪🇸', name: 'Espagne' },
    { code: '+39', flag: '🇮🇹', name: 'Italie' },
    { code: '+351', flag: '🇵🇹', name: 'Portugal' },
    { code: '+31', flag: '🇳🇱', name: 'Pays-Bas' },
    { code: '+46', flag: '🇸🇪', name: 'Suède' },
    { code: '+47', flag: '🇳🇴', name: 'Norvège' },
    { code: '+48', flag: '🇵🇱', name: 'Pologne' },
    { code: '+90', flag: '🇹🇷', name: 'Turquie' },
    { code: '+7', flag: '🇷🇺', name: 'Russie' },
    { code: '+966', flag: '🇸🇦', name: 'Arabie Saoudite' },
    { code: '+971', flag: '🇦🇪', name: 'Émirats Arabes Unis' },
    { code: '+974', flag: '🇶🇦', name: 'Qatar' },
    { code: '+965', flag: '🇰🇼', name: 'Koweït' },
    { code: '+961', flag: '🇱🇧', name: 'Liban' },
    { code: '+86', flag: '🇨🇳', name: 'Chine' },
    { code: '+91', flag: '🇮🇳', name: 'Inde' },
    { code: '+81', flag: '🇯🇵', name: 'Japon' },
    { code: '+82', flag: '🇰🇷', name: 'Corée du Sud' },
    { code: '+55', flag: '🇧🇷', name: 'Brésil' },
    { code: '+52', flag: '🇲🇽', name: 'Mexique' },
    { code: '+54', flag: '🇦🇷', name: 'Argentine' },
    { code: '+57', flag: '🇨🇴', name: 'Colombie' },
    { code: '+61', flag: '🇦🇺', name: 'Australie' },
    { code: '+65', flag: '🇸🇬', name: 'Singapour' },
    { code: '+60', flag: '🇲🇾', name: 'Malaisie' },
    { code: '+62', flag: '🇮🇩', name: 'Indonésie' },
    { code: '+63', flag: '🇵🇭', name: 'Philippines' },
    { code: '+66', flag: '🇹🇭', name: 'Thaïlande' },
    { code: '+84', flag: '🇻🇳', name: 'Vietnam' },
];

let countryDropdownOpen = false;

function toggleCountryDropdown() {
    const dd = document.getElementById('phoneCountryDropdown');
    countryDropdownOpen = !countryDropdownOpen;
    dd.classList.toggle('show', countryDropdownOpen);
    if (countryDropdownOpen) {
        const search = document.getElementById('phoneCountrySearch');
        search.value = '';
        search.focus();
        renderCountryList('');
    }
}

function renderCountryList(filter) {
    const list = document.getElementById('phoneCountryList');
    const f = filter.toLowerCase();
    const filtered = phoneCountries.filter(c =>
        c.name.toLowerCase().includes(f) || c.code.includes(f)
    );
    list.innerHTML = filtered.map(c =>
        `<div class="phone-country-item" onclick="selectCountry('${c.code}','${c.flag}','${c.name}')">
            <span class="country-flag">${c.flag}</span>
            <span class="country-name">${c.name}</span>
            <span class="country-code">${c.code}</span>
        </div>`
    ).join('');
}

function selectCountry(code, flag, name) {
    document.getElementById('phone_country').value = code;
    document.getElementById('phoneCountryDisplay').textContent = `${flag} ${name} ${code}`;
    document.getElementById('phoneCountryDropdown').classList.remove('show');
    countryDropdownOpen = false;
    document.getElementById('phone').focus();
}

// Search filter
document.getElementById('phoneCountrySearch').addEventListener('input', function() {
    renderCountryList(this.value);
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.phone-country-wrapper')) {
        document.getElementById('phoneCountryDropdown').classList.remove('show');
        countryDropdownOpen = false;
    }
});

// Initialize with saved value
(function() {
    const saved = document.getElementById('phone_country').value || '+221';
    const c = phoneCountries.find(x => x.code === saved) || phoneCountries[0];
    document.getElementById('phoneCountryDisplay').textContent = `${c.flag} ${c.name} ${c.code}`;
})();

function toggleFrequency(show) {
    document.getElementById('frequencySection').style.display = show ? 'block' : 'none';
    if (show) {
        document.getElementById('frequency').setAttribute('required', 'required');
    } else {
        document.getElementById('frequency').removeAttribute('required');
    }
}

function selectAmount(amount) {
    document.getElementById('amount').value = amount;
    document.querySelectorAll('.amount-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    updateUsdEstimate();
}

function selectProvider(provider) {
    document.getElementById('payment_provider').value = provider;

    // Update payment methods based on provider
    document.querySelectorAll('input[name="payment_method"]').forEach(input => {
        input.checked = false;
    });

    if (provider === 'bictorys') {
        document.getElementById('method_bictorys_orange_money').checked = true;
    } else {
        document.getElementById('paypal_paypal_balance').checked = true;
    }
}

function updateUsdEstimate() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const usdAmount = (amount / 600).toFixed(2); // Approximate conversion rate
    document.getElementById('usdAmount').textContent = usdAmount;
}

// Form submission
document.getElementById('donationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const modal = new bootstrap.Modal(document.getElementById('processingModal'));
    modal.show();

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        modal.hide();

        if (data.success) {
            if (data.payment_provider === 'paypal' && data.approve_url) {
                window.location.href = data.approve_url;
            } else if (data.payment_url) {
                window.location.href = data.payment_url;
            } else {
                window.location.href = '{{ route("donations.success", ["donation" => ":id"]) }}'.replace(':id', data.donation_id);
            }
        } else {
            alert(data.message || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        modal.hide();
        console.error('Error:', error);
        alert('Erreur de connexion. Veuillez réessayer.');
    });
});

// Update USD estimate on amount change
document.getElementById('amount').addEventListener('input', updateUsdEstimate);
updateUsdEstimate();
</script>
@endpush
