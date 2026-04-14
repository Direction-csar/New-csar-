@extends('layouts.public')

@section('title', __('messages.donations.title'))
@section('meta_description', __('messages.donations.subtitle'))

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #0d3b5c 0%, #1e5a8a 100%); color: #fff;">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-3">{{ __('messages.donations.title') }}</h1>
        <p class="lead mb-0">{{ __('messages.donations.subtitle') }}</p>
    </div>
</section>

<!-- Donation Description -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h2 class="h3 mb-4" style="color: #0d3b5c;">{{ __('messages.donations.title') }}</h2>
                <p class="text-muted mb-4">{{ __('messages.donations.description') }}</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Wave</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Orange Money</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ __('messages.donations.payment_methods.credit_card') }}</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> PayPal</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <h3 class="h5 mb-3" style="color: #0d3b5c;">{{ __('messages.donations.form.payment_method') }}</h3>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <span class="badge bg-primary p-2"><i class="fas fa-mobile-alt me-1"></i> Wave</span>
                            <span class="badge bg-warning text-dark p-2"><i class="fas fa-mobile-alt me-1"></i> Orange Money</span>
                            <span class="badge bg-info text-white p-2"><i class="fas fa-credit-card me-1"></i> {{ __('messages.donations.payment_methods.credit_card') }}</span>
                            <span class="badge bg-secondary p-2"><i class="fab fa-paypal me-1"></i> PayPal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donation Form Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="card-body p-5">
                        <h3 class="h4 mb-4 text-center" style="color: #0d3b5c;">{{ __('messages.donations.form.personal_info') }}</h3>
                        
                        <form id="donationForm" method="POST" action="{{ route('donations.process') }}">
                            @csrf
                            
                            <!-- Personal Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">{{ __('messages.donations.form.full_name') }} *</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" required value="{{ old('full_name') }}">
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('messages.donations.form.email') }} *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" required value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">{{ __('messages.donations.form.phone') }}</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="donation_type" class="form-label">{{ __('messages.donations.form.donation_type') }} *</label>
                                    <select class="form-select @error('donation_type') is-invalid @enderror" id="donation_type" name="donation_type" required>
                                        <option value="single" {{ old('donation_type') == 'single' ? 'selected' : '' }}>{{ __('messages.donations.donation_types.single') }}</option>
                                        <option value="monthly" {{ old('donation_type') == 'monthly' ? 'selected' : '' }}>{{ __('messages.donations.donation_types.monthly') }}</option>
                                    </select>
                                    @error('donation_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Donation Amount -->
                            <div class="mb-4">
                                <label class="form-label">{{ __('messages.donations.suggested_amounts') }}</label>
                                <div class="row g-2 mb-3">
                                    <div class="col-3">
                                        <button type="button" class="btn btn-outline-primary amount-btn w-100" data-amount="1000">1,000</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-outline-primary amount-btn w-100" data-amount="5000">5,000</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-outline-primary amount-btn w-100" data-amount="10000">10,000</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-outline-primary amount-btn w-100" data-amount="50000">50,000</button>
                                    </div>
                                </div>
                                <label for="amount" class="form-label">{{ __('messages.donations.form.amount') }} *</label>
                                <div class="input-group">
                                    <span class="input-group-text">XOF</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" min="500" max="1000000" required value="{{ old('amount') }}">
                                </div>
                                <div class="form-text">{{ __('messages.donations.validation.amount_min', ['min' => 500]) }}</div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-4">
                                <label class="form-label d-block">{{ __('messages.donations.form.payment_method') }} *</label>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="wave" value="wave" required {{ old('payment_method') == 'wave' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="wave">
                                                <i class="fas fa-mobile-alt text-primary me-1"></i> {{ __('messages.donations.payment_methods.wave') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="orange_money" value="orange_money" required {{ old('payment_method') == 'orange_money' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="orange_money">
                                                <i class="fas fa-mobile-alt text-warning me-1"></i> {{ __('messages.donations.payment_methods.orange_money') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" required {{ old('payment_method') == 'credit_card' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="credit_card">
                                                <i class="fas fa-credit-card text-info me-1"></i> {{ __('messages.donations.payment_methods.credit_card') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" required {{ old('payment_method') == 'paypal' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="paypal">
                                                <i class="fab fa-paypal text-primary me-1"></i> PayPal
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('payment_method')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label for="message" class="form-label">{{ __('messages.donations.form.message') }}</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="3">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Anonymous Donation -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_anonymous">
                                        {{ __('messages.donations.form.is_anonymous') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-danger btn-lg px-5" id="submitBtn">
                                    <i class="fas fa-heart me-2"></i>
                                    <span id="submitText">{{ __('messages.donations.form.submit') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Track Donation Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-4 text-center" style="color: #0d3b5c;">{{ __('messages.donations.track.title') }}</h3>
                        <form method="GET" action="{{ route('donations.track') }}">
                            <div class="mb-3">
                                <label for="track_email" class="form-label">{{ __('messages.donations.track.email') }} *</label>
                                <input type="email" class="form-control" id="track_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="transaction_id" class="form-label">{{ __('messages.donations.track.transaction_id') }}</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search me-2"></i>{{ __('messages.donations.track.search') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="d-none">
    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.7); z-index: 9999;">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status"></div>
            <div>{{ __('messages.donations.form.processing') }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Amount buttons
    const amountBtns = document.querySelectorAll('.amount-btn');
    const amountInput = document.getElementById('amount');
    
    amountBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            amountInput.value = this.dataset.amount;
            amountBtns.forEach(b => b.classList.remove('active', 'btn-primary'));
            amountBtns.forEach(b => b.classList.add('btn-outline-primary'));
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary', 'active');
        });
    });
    
    // Set active button if amount matches
    if (amountInput.value) {
        amountBtns.forEach(btn => {
            if (btn.dataset.amount === amountInput.value) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary', 'active');
            }
        });
    }
    
    // Form submission
    const form = document.getElementById('donationForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate amount
        const amount = parseFloat(amountInput.value);
        if (!amount || amount < 500 || amount > 1000000) {
            alert('{{ __("messages.donations.validation.amount_min", ["min" => 500]) }}');
            return;
        }
        
        // Validate payment method
        const paymentMethod = form.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethod) {
            alert('{{ __("messages.donations.validation.required") }}');
            return;
        }
        
        // Show loading
        loadingOverlay.classList.remove('d-none');
        submitBtn.disabled = true;
        submitText.textContent = '{{ __("messages.donations.form.processing") }}';
        
        // Submit form
        const formData = new FormData(form);
        
        fetch('{{ route("donations.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to payment URL or success page
                if (data.payment_url) {
                    window.location.href = data.payment_url;
                } else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            } else {
                // Show error
                alert(data.message || '{{ __("messages.donations.errors.processing_error") }}');
                loadingOverlay.classList.add('d-none');
                submitBtn.disabled = false;
                submitText.textContent = '{{ __("messages.donations.form.submit") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("messages.donations.errors.processing_error") }}');
            loadingOverlay.classList.add('d-none');
            submitBtn.disabled = false;
            submitText.textContent = '{{ __("messages.donations.form.submit") }}';
        });
    });
});
</script>

<style>
.amount-btn {
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.amount-btn:hover {
    background-color: #0d3b5c;
    color: white;
    border-color: #0d3b5c;
}

.amount-btn.active {
    background-color: #0d3b5c;
    border-color: #0d3b5c;
    color: white;
}

.form-check-input:checked {
    background-color: #0d3b5c;
    border-color: #0d3b5c;
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.form-control:focus, .form-select:focus {
    border-color: #0d3b5c;
    box-shadow: 0 0 0 0.2rem rgba(13, 59, 92, 0.25);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>
@endpush
