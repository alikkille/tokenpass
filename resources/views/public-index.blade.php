@extends('layouts.guest')

@section('body_content')

<div class="landing-nav-menu-mobile" id="mobile-nav">
  <div class="landing-nav-menu-header"><i class="material-icons toggle-mobile-nav">close</i></div>
  <ul>
    <li><a href="/auth/register">Register</a></li>
    <li><a href="/auth/login">Login</a></li>
  </ul>
</div>
<div class="landing-nav pristine">
  <div class="landing-nav-container"><a href="/"><span class="logo">token<strong>pass</strong></span></a>
    <div class="landing-nav-menu"><a class="btn-register" href="/auth/register">Register</a><a class="btn-login" href="/auth/login">Login</a></div>
    <div class="landing-nav-menu-btn"><i class="material-icons toggle-mobile-nav">dehaze</i></div>
  </div>
</div>
<div class="hero">
  <div class="hero-flex-content">
    <h1 class="hero-heading">Digital access tokens in your bitcoin wallet.</h1>
    <button class="btn-cta">Get Started</button>
  </div>
  <div class="hero-bg" style="background-image: url(/img/landing_hero.png)"></div>
</div>
<div class="mission-area">
  <div class="content-wrapper">
    <div class="mission-heading">Tokens give you special features and privileges based on the contents of your bitcoin wallet.</div>
  </div>
</div>
<div class="how-area">
  <div class="content-wrapper">
    <div class="how-heading">How It Works</div>
    <div class="how-content">
      <div class="how-token-modules">
        <div class="title">Tokens can be represented in two ways…</div>
        <div class="module">
          <div class="heading"><span>Redeemables</span></div>
          <div class="node"><span>></span></div>
          <div class="description"><span>…as something redeemable - think ‘digital gift certificate</span></div>
        </div>
        <div class="module">
          <div class="heading"><span>Access Tokens</span></div>
          <div class="node"><span>></span></div>
          <div class="description"><span>…or as an Access Token that gives holders entry to features, services or accounts they wouldn’t have access to otherwise.</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="feature-area">
  <div class="feature-heading">Features</div>
  <div class="features">
    <div class="feature">
      <div class="icon"><i class="material-icons">visibility</i></div>
      <div class="title">Privacy Control</div>
      <div class="subtext">Control which wallet addresses are visible to the world.</div>
    </div>
    <div class="feature">
      <div class="icon"><i class="material-icons">radio_button_on</i></div>
      <div class="title">Inventory Management</div>
      <div class="subtext">Browse and search your collection of access tokens. </div>
    </div>
    <div class="feature">
      <div class="icon"><i class="material-icons">lock_outline</i></div>
      <div class="title">Secure Access</div>
      <div class="subtext">Authentication using TCA is secure with cryptocurrency tech.</div>
    </div>
  </div>
</div>
<div class="why-area">
  <div class="content-wrapper">
    <div class="why-heading">Why Use Tokens?</div>
    <div class="why-content">
      <p><strong>> Want to give supports from your pre-sale access to exclusive updates to your project?</strong><br><span>Create a token that controls access to a private blog and distribute to anyone who holds you pre-sale token.</span></p>
      <p><strong>> Are you an artist who wants to give early access to your followers?</strong><br><span>Just create a token and give your followers instructions on how to retrieve it. </span></p>
      <p><span>Token Controlled Access allows for limitless possibilities when engaging with your audience. Create acess tokens for games, business, or anything that provides value to someone. </span></p>
    </div>
  </div>
</div>
<div class="footer">
  <div class="footer-content">
    <span>&copy; Tokenly 2016</span>
  </div>
</div>

@endsection
