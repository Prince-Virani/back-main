@extends('layout.empty')

@section('title', 'Login')
@section('content')
<style>
    :root {
        --primary-color: #0071e3;
        --primary-hover: #0077ED;
        --text-primary: #1d1d1f;
        --text-secondary: #86868b;
        --border-color: #d2d2d7;
        --overlay-color: rgba(0, 0, 0, 0.5);
        --card-bg: rgba(255, 255, 255, 0.95);
        --error-color: #ff3b30;
        --success-bg: #e5f5eb;
        --success-border: #34c759;
    }

    .login-container {
        min-height: 100vh;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        overflow: hidden;
        background-color: #000; /* Fallback */
    }

    .background-slideshow {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1.5s ease-in-out;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        will-change: opacity;
        transform: translateZ(0);
    }

    .slide.active {
        opacity: 1;
    }

    .slide::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--overlay-color);
        backdrop-filter: contrast(1.1);
    }

    .login-card {
        background: var(--card-bg);
        backdrop-filter: blur(10px) saturate(180%);
        -webkit-backdrop-filter: blur(10px) saturate(180%);
        border-radius: 24px;
        padding: 3rem;
        width: 100%;
        max-width: 440px;
        box-shadow: 
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease;
    }

    .login-card:hover {
        transform: translateY(-2px);
    }

    .login-title {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        text-align: center;
        letter-spacing: -0.025em;
    }

    .login-subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
        margin-bottom: 2rem;
        text-align: center;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        color: var(--text-primary);
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control {
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        transition: all 0.2s ease;
        width: 100%;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.1);
        outline: none;
    }

    .form-control::placeholder {
        color: var(--text-secondary);
        opacity: 0.7;
    }

    .btn-sign-in {
        background: var(--primary-color);
        border: none;
        border-radius: 12px;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 600;
        color: white;
        width: 100%;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .btn-sign-in:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-sign-in:active {
        transform: translateY(0);
    }

    .text-red {
        color: var(--error-color);
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: block;
    }

    .alert {
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    .alert-success {
        background: var(--success-bg);
        border: 1px solid var(--success-border);
        color: var(--text-primary);
    }

    /* Loading indicator styles */
    .loading-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 12px;
    padding: 12px 20px;
    color: white;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1000;
    transform: translateY(100px);
    transition: transform 0.3s ease;
    }
    
    .loading-indicator.visible {
        transform: translateY(0);
    }
    
    .loading-dots {
        display: flex;
        gap: 4px;
    }
    
    .loading-dots span {
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        animation: dotPulse 1.4s infinite;
    }
    
    .loading-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .loading-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }
    
    @keyframes dotPulse {
        0%, 100% { transform: scale(0.5); opacity: 0.5; }
        50% { transform: scale(1); opacity: 1; }
    }
    
    .slide {
        opacity: 0;
        transition: opacity 2s ease-in-out;
        background: #000;  /* Dark background while loading */
    }
    
    .slide.preloading {
        opacity: 0.3;  /* Slightly visible while loading */
    }
    
    .slide.active {
        opacity: 1;
    }
    
    .background-initial {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, #1a1a1a, #2d2d2d);
        z-index: -2;
    }
    
    .logo-container {
        text-align: center;
        margin-bottom: 1.5rem;
        margin-left: -2rem;
    }

    .logo-container img {
        max-height: 50px;
        max-width: 300px; /* Add size limit */
        width: auto;
        object-fit: contain;
    }

</style>


<div class="login-container">
   <div class="background-initial"></div>
    <div class="background-slideshow">
        <div class="slide"></div>
        <div class="slide"></div>
    </div>
    
    <div class="loading-indicator">
        <span>Loading backgrounds</span>
        <div class="loading-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    
    <div class="login-card">
        <form method="POST" action="{{ route('login') }}" name="login_form">
            @csrf
            <div class="logo-container">
                <img src="/assets/img/ever_glow.png" alt="Ever Glow Logo">
            </div>
			@if (session('error'))
				<div class="alert alert-danger text-center">{{ session('error') }}</div>
			@endif
            
            <div class="login-subtitle">
                {{ __('For your protection, please verify your identity.') }}
            </div>
            <div class="form-group">
                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                <input type="email"
					name="email"
					class="form-control form-control-lg fs-15px @error('email') is-invalid @enderror"
					value="{{ old('email') ?? Cookie::get('admin_email') }}"
					placeholder="username@address.com"
					required>
				@error('email')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
            </div>
            <div class="form-group">
			<label class="form-label" for="password">{{ __('Password') }}</label>
			<div class="input-group">
			 <input type="password" name="password" id="password" class="form-control form-control-lg fs-15px @error('password') is-invalid @enderror" value="{{ old('password') ?? Cookie::get('admin_password') }}"
						placeholder="Enter your password"
						required>
					<button type="button" class="btn btn-outline-secondary" id="togglePassword">👁</button>
				</div>
				@error('password')
					<div class="invalid-feedback d-block">{{ $message }}</div>
				@enderror
            </div>
			<button type="submit" class="btn-sign-in">Sign In</button>
        </form>
    </div>
</div>



<script>
	class BackgroundSlideshow {
    constructor() {
        this.slides = document.querySelectorAll('.slide');
        this.currentSlide = 0;
        this.imageCache = new Map();
        this.loadingIndicator = document.querySelector('.loading-indicator');
        this.isTransitioning = false;
        this.loadedImages = 0;
        
        // Curated landscape images (all verified working)
        this.images = [
            'https://images.unsplash.com/photo-1466854076813-4aa9ac0fc347',
            'https://images.unsplash.com/photo-1744762567611-dee68f42ecc9',
            'https://images.unsplash.com/photo-1473800447596-01729482b8eb',
			'https://images.unsplash.com/photo-1511884642898-4c92249e20b6',
			'https://images.unsplash.com/photo-1466129646777-494b376a670c',
			'https://images.unsplash.com/photo-1502786129293-79981df4e689',
            
        ];

        this.init();
    }

    async init() {
        this.showLoading();
        this.startBackgroundLoading();
    }

    showLoading() {
        this.loadingIndicator.classList.add('visible');
    }

    hideLoading() {
        this.loadingIndicator.classList.remove('visible');
        setTimeout(() => {
            this.loadingIndicator.style.display = 'none';
        }, 300);
    }

    updateLoadingProgress() {
        this.loadedImages++;
        if (this.loadedImages === this.images.length) {
            this.hideLoading();
        }
    }

    formatImageUrl(url) {
        return `${url}?auto=format&fit=crop&w=1920&h=1080&q=80`;
    }

    async startBackgroundLoading() {
        // Start loading first image immediately
        this.preloadImage(this.images[0]).then(url => {
            this.setInitialBackground(url);
        });

        // Load the rest in the background
        for (let i = 1; i < this.images.length; i++) {
            this.preloadImage(this.images[i]);
        }

        // Start slideshow once we have at least 2 images
        const checkAndStartSlideshow = () => {
            if (this.imageCache.size >= 2 && !this.slideshowStarted) {
                this.slideshowStarted = true;
                setInterval(() => this.changeBackground(), 5000);
            }
        };

        // Check every second until we can start
        const slideshowCheck = setInterval(() => {
            checkAndStartSlideshow();
            if (this.slideshowStarted) {
                clearInterval(slideshowCheck);
            }
        }, 1000);
    }

    preloadImage(url) {
        return new Promise((resolve, reject) => {
            if (this.imageCache.has(url)) {
                resolve(url);
                return;
            }

            const img = new Image();
            const formattedUrl = this.formatImageUrl(url);
            
            img.onload = () => {
                this.imageCache.set(url, formattedUrl);
                this.updateLoadingProgress();
                resolve(url);
            };
            
            img.onerror = () => {
                console.error(`Failed to load image: ${url}`);
                this.updateLoadingProgress();
                reject(new Error(`Failed to load image: ${url}`));
            };
            
            img.src = formattedUrl;
        });
    }

    setInitialBackground(imageUrl) {
        const formattedUrl = this.imageCache.get(imageUrl);
        if (formattedUrl) {
            this.slides[0].classList.add('preloading');
            this.slides[0].style.backgroundImage = `url(${formattedUrl})`;
            setTimeout(() => {
                this.slides[0].classList.remove('preloading');
                this.slides[0].classList.add('active');
            }, 100);
        }
    }

    async changeBackground() {
        if (this.isTransitioning || this.imageCache.size < 2) return;
        this.isTransitioning = true;

        const nextSlide = this.slides[this.currentSlide === 0 ? 1 : 0];
        const currentSlide = this.slides[this.currentSlide];

        // Get a random cached image that's not currently displayed
        const availableImages = Array.from(this.imageCache.keys())
            .filter(url => url !== currentSlide.style.backgroundImage);
        
        if (availableImages.length === 0) return;

        const randomImage = availableImages[Math.floor(Math.random() * availableImages.length)];
        const formattedUrl = this.imageCache.get(randomImage);

        nextSlide.style.backgroundImage = `url(${formattedUrl})`;
        nextSlide.classList.add('preloading');
        
        // Small delay to ensure new background is ready
        setTimeout(() => {
            nextSlide.classList.remove('preloading');
            nextSlide.classList.add('active');
            currentSlide.classList.remove('active');
            
            this.currentSlide = this.currentSlide === 0 ? 1 : 0;
            this.isTransitioning = false;
        }, 50);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new BackgroundSlideshow();
});
document.getElementById('togglePassword').addEventListener('click', function () {

        let passwordField = document.getElementById('password');

        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';

});

</script>

@endsection





