<script setup lang="ts">
useHead({
  title: 'About Us | NOVACART',
  meta: [{ name: 'description', content: 'Learn about NOVACART, our story and our commitment to better online shopping.' }]
})

const values = [
  { icon: 'bi-patch-check', title: 'Quality first', text: 'We select dependable products that offer genuine value and everyday usefulness.' },
  { icon: 'bi-people', title: 'Customer focused', text: 'Every decision starts with making shopping simpler, clearer and more supportive.' },
  { icon: 'bi-shield-check', title: 'Built on trust', text: 'Straightforward pricing, secure payments and honest communication at every step.' },
  { icon: 'bi-globe-asia-australia', title: 'Local understanding', text: 'A shopping experience designed around the needs of customers across Bangladesh.' }
]
</script>

<template>
  <main>
    <section class="about-hero">
      <div class="container about-hero-grid">
        <div class="about-hero-copy">
          <span class="about-eyebrow">Our story</span>
          <h1>Better products.<br><em>Better everyday living.</em></h1>
          <p>NOVACART brings thoughtfully selected technology, fashion and home essentials together in one dependable shopping experience.</p>
          <div><NuxtLink class="about-primary" to="/shop">Explore our products <i class="bi bi-arrow-right"></i></NuxtLink><NuxtLink class="about-secondary" to="/contact">Talk to our team</NuxtLink></div>
        </div>
        <div class="about-hero-visual" aria-hidden="true">
          <div class="about-visual-card card-one"><i class="bi bi-headphones"></i><span>Everyday technology</span></div>
          <div class="about-visual-card card-two"><i class="bi bi-house-heart"></i><span>Better living</span></div>
          <div class="about-visual-card card-three"><i class="bi bi-bag"></i><span>Considered style</span></div>
          <div class="about-visual-mark">N<span>C</span></div>
        </div>
      </div>
    </section>

    <div class="shop-breadcrumb"><div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><span>About Us</span></div></div>

    <section class="about-intro">
      <div class="container about-intro-grid">
        <div><span class="about-eyebrow">Who we are</span><h2>Shopping should feel simple, useful and trustworthy.</h2></div>
        <div><p>We started NOVACART with a clear idea: online shopping should help people make confident choices without unnecessary complexity. That means useful products, clear information and support that is easy to reach.</p><p>From daily essentials to exciting upgrades, we focus on products that fit real lives—and service that continues after checkout.</p></div>
      </div>
    </section>

    <section class="about-stats">
      <div class="container">
        <div><strong>10k+</strong><span>Happy customers</span></div><div><strong>1,500+</strong><span>Selected products</span></div><div><strong>64</strong><span>Districts served</span></div><div><strong>7 days</strong><span>Easy returns</span></div>
      </div>
    </section>

    <section class="about-values">
      <div class="container">
        <div class="about-section-head"><div><span class="about-eyebrow">What guides us</span><h2>Values behind every order</h2></div><p>These principles shape the products we choose and the experience we create.</p></div>
        <div class="about-value-grid">
          <article v-for="(value, index) in values" :key="value.title"><span>0{{ index + 1 }}</span><i class="bi" :class="value.icon"></i><h3>{{ value.title }}</h3><p>{{ value.text }}</p></article>
        </div>
      </div>
    </section>

    <section class="about-promise">
      <div class="container about-promise-grid">
        <div class="about-promise-visual"><div><i class="bi bi-box-seam"></i></div><span><b>Carefully packed</b><small>Prepared with attention for a safe arrival</small></span></div>
        <div class="about-promise-copy"><span class="about-eyebrow">Our promise</span><h2>With you before, during and after every purchase.</h2><p>A great store does more than deliver products. We make it easy to get product information, delivery assistance and return support whenever you need it.</p>
          <ul><li><i class="bi bi-check2-circle"></i> Clear product information</li><li><i class="bi bi-check2-circle"></i> Secure and flexible checkout</li><li><i class="bi bi-check2-circle"></i> Nationwide delivery support</li><li><i class="bi bi-check2-circle"></i> Friendly after-sales care</li></ul>
        </div>
      </div>
    </section>

    <section class="about-cta"><div class="container"><div><span class="about-eyebrow">Discover NOVACART</span><h2>Find something made for your everyday.</h2></div><NuxtLink to="/shop">Start shopping <i class="bi bi-arrow-right"></i></NuxtLink></div></section>
  </main>
</template>

<style scoped>
.about-eyebrow{display:block;color:var(--brand);font-size:.68rem;font-weight:800;letter-spacing:.17em;text-transform:uppercase}.about-hero{overflow:hidden;background:#f2f6f2}.about-hero-grid{display:grid;grid-template-columns:1.18fr .82fr;min-height:455px}.about-hero-copy{align-self:center;padding:52px 65px 52px 0}.about-hero-copy h1{max-width:680px;margin:12px 0 18px;font-size:clamp(2.7rem,4.2vw,4.45rem);font-weight:800;line-height:1.01;letter-spacing:-.045em}.about-hero-copy h1 em{color:var(--brand);font-style:normal}.about-hero-copy>p{max-width:620px;color:#66716b;font-size:.96rem;line-height:1.75}.about-hero-copy>div{display:flex;align-items:center;gap:22px;margin-top:25px}.about-primary{background:var(--brand);padding:13px 21px;color:#fff;text-decoration:none;font-size:.72rem;font-weight:800;text-transform:uppercase}.about-primary i{margin-left:8px}.about-secondary{border-bottom:1px solid var(--ink);color:var(--ink);text-decoration:none;font-size:.78rem;font-weight:700}.about-hero-visual{position:relative;min-height:455px;background:linear-gradient(145deg,#17211d,#29372f)}.about-hero-visual:before,.about-hero-visual:after{position:absolute;border:1px solid rgba(255,255,255,.1);border-radius:50%;content:""}.about-hero-visual:before{inset:8% -20% 8% 5%}.about-hero-visual:after{inset:23% -5% 23% 23%}.about-visual-mark{position:absolute;top:50%;left:50%;color:#fff;font-size:5rem;font-weight:900;letter-spacing:-.12em;transform:translate(-50%,-50%)}.about-visual-mark span{color:var(--brand)}.about-visual-card{position:absolute;z-index:1;display:flex;align-items:center;gap:8px;background:#fff;padding:11px 14px;box-shadow:0 12px 28px rgba(0,0,0,.16);font-size:.67rem;font-weight:700}.about-visual-card i{color:var(--brand);font-size:1.05rem}.card-one{top:13%;left:8%}.card-two{right:7%;bottom:12%}.card-three{bottom:17%;left:6%}
.about-intro{padding:80px 0}.about-intro-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:75px}.about-intro h2{max-width:560px;margin:13px 0 0;font-size:clamp(2.1rem,3.4vw,3.4rem);line-height:1.1}.about-intro p{color:#66716b;font-size:.96rem;line-height:1.85}.about-stats{background:#17211d;color:#fff}.about-stats .container{display:grid;grid-template-columns:repeat(4,1fr)}.about-stats .container>div{padding:35px 25px;border-right:1px solid rgba(255,255,255,.1);text-align:center}.about-stats .container>div:first-child{border-left:1px solid rgba(255,255,255,.1)}.about-stats strong,.about-stats span{display:block}.about-stats strong{color:var(--brand);font-size:2rem}.about-stats span{margin-top:5px;color:#b8c2bd;font-size:.72rem;text-transform:uppercase}
.about-values{padding:80px 0;background:#f8faf8}.about-section-head{display:flex;justify-content:space-between;align-items:end;gap:40px;margin-bottom:36px}.about-section-head h2{margin:10px 0 0;font-size:clamp(2rem,3.4vw,3.2rem)}.about-section-head>p{max-width:430px;margin:0;color:#748079;line-height:1.7}.about-value-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.about-value-grid article{position:relative;min-height:265px;border:1px solid #e1e6e3;background:#fff;padding:28px 25px;transition:.25s}.about-value-grid article:hover{border-color:rgba(255,89,65,.45);transform:translateY(-5px);box-shadow:0 16px 35px rgba(23,33,29,.07)}.about-value-grid article>span{position:absolute;top:22px;right:24px;color:#d4dad6;font-weight:800}.about-value-grid article>i{display:grid;width:48px;height:48px;place-items:center;background:#fff0ec;color:var(--brand);font-size:1.25rem}.about-value-grid h3{margin:30px 0 10px;font-size:1.1rem}.about-value-grid p{margin:0;color:#76817b;font-size:.82rem;line-height:1.7}
.about-promise{padding:105px 0}.about-promise-grid{display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:90px}.about-promise-visual{position:relative;display:grid;min-height:470px;place-items:center;background:linear-gradient(135deg,#fff0e9,#e8f1ec)}.about-promise-visual>div{display:grid;width:190px;height:190px;border-radius:50%;place-items:center;background:#fff;color:var(--brand);box-shadow:0 25px 60px rgba(23,33,29,.12);font-size:5rem}.about-promise-visual>span{position:absolute;right:25px;bottom:25px;max-width:270px;background:#17211d;padding:20px 24px;color:#fff}.about-promise-visual b,.about-promise-visual small{display:block}.about-promise-visual small{margin-top:4px;color:#aeb9b3}.about-promise-copy h2{margin:12px 0 20px;font-size:clamp(2.1rem,4vw,3.7rem);line-height:1.08}.about-promise-copy>p{color:#6d7872;line-height:1.8}.about-promise-copy ul{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:30px 0 0;padding:0;list-style:none}.about-promise-copy li{font-size:.82rem;font-weight:700}.about-promise-copy li i{margin-right:7px;color:var(--brand)}.about-cta{padding:70px 0;background:#fff3ee}.about-cta .container{display:flex;justify-content:space-between;align-items:center;gap:30px}.about-cta h2{margin:8px 0 0;font-size:clamp(1.8rem,4vw,3.2rem)}.about-cta a{flex:0 0 auto;background:var(--brand);padding:15px 24px;color:#fff;text-decoration:none;font-size:.78rem;font-weight:800;text-transform:uppercase}.about-cta a i{margin-left:8px}
@media(max-width:991px){.about-hero-grid{grid-template-columns:1fr}.about-hero-copy{padding:55px 0}.about-hero-visual{min-height:360px}.about-intro-grid,.about-promise-grid{grid-template-columns:1fr;gap:45px}.about-value-grid{grid-template-columns:repeat(2,1fr)}.about-promise-copy{order:-1}}
@media(max-width:575px){.about-hero-copy{padding:45px 0}.about-hero-copy h1{font-size:2.65rem}.about-hero-copy>div{align-items:flex-start;flex-direction:column}.about-hero-visual{min-height:285px}.about-visual-mark{font-size:4rem}.about-visual-card{padding:9px 11px}.card-three{display:none}.about-intro,.about-values,.about-promise{padding:60px 0}.about-stats .container,.about-value-grid{grid-template-columns:1fr 1fr}.about-stats .container>div{padding:25px 8px}.about-stats strong{font-size:1.5rem}.about-section-head{align-items:flex-start;flex-direction:column}.about-value-grid{gap:10px}.about-value-grid article{min-height:245px;padding:22px 16px}.about-value-grid h3{margin-top:24px}.about-promise-visual{min-height:360px}.about-promise-visual>div{width:145px;height:145px;font-size:3.5rem}.about-promise-visual>span{right:15px;bottom:15px;left:15px}.about-promise-copy ul{grid-template-columns:1fr}.about-cta .container{align-items:flex-start;flex-direction:column}}
</style>
