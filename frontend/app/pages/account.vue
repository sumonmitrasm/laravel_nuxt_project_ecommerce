<script setup lang="ts">
const route = useRoute()
const activeSection = ref('dashboard')
const { user, logout } = useAuth()
const isLoggingOut = ref(false)

definePageMeta({ middleware: 'auth' })

const displayName = computed(() => user.value?.name || 'Customer')
const firstName = computed(() => displayName.value.trim().split(/\s+/)[0] || 'Customer')
const initials = computed(() => user.value?.name.trim().split(/\s+/).slice(0, 2).map(part => part[0]?.toUpperCase()).join('') || 'NC')

const handleLogout = async () => {
  if (isLoggingOut.value) return

  isLoggingOut.value = true
  try {
    await logout()
    await navigateTo('/login')
  } finally {
    isLoggingOut.value = false
  }
}

const sections = [
  { id: 'dashboard', label: 'Dashboard', icon: 'bi-grid' },
  { id: 'orders', label: 'My orders', icon: 'bi-box-seam' },
  { id: 'address', label: 'Addresses', icon: 'bi-geo-alt' },
  { id: 'profile', label: 'Account details', icon: 'bi-person' }
]

const orders = [
  { id: '#NC-10482', date: '12 Aug 2026', total: 'à§³15,480', items: '2 items', status: 'Processing', tone: 'processing' },
  { id: '#NC-10391', date: '28 Jul 2026', total: 'à§³8,490', items: '1 item', status: 'Delivered', tone: 'delivered' },
  { id: '#NC-10224', date: '05 Jul 2026', total: 'à§³6,990', items: '1 item', status: 'Delivered', tone: 'delivered' }
]

const profileSaved = ref(false)

useHead({
  title: 'My Account | NOVACART',
  meta: [{ name: 'description', content: 'Manage your NOVACART orders, addresses and account details.' }]
})
</script>

<template>
  <NuxtPage v-if="route.path.startsWith('/account/')" />
  <main v-else class="customer-account">
    <section class="account-banner">
      <div class="container"><div><span>My account</span><h1>Welcome back, {{ firstName }}.</h1><p>Manage your orders, saved details and shopping activity.</p></div><div class="account-avatar">{{ initials }}</div></div>
    </section>

    <div class="shop-breadcrumb"><div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><span>My Account</span></div></div>

    <section class="account-dashboard container">
      <div class="account-mobile-tabs">
        <button v-for="section in sections" :key="section.id" type="button" :class="{ active: activeSection === section.id }" @click="activeSection = section.id"><i class="bi" :class="section.icon"></i>{{ section.label }}</button>
      </div>

      <div class="account-layout">
        <aside class="account-sidebar">
          <div class="account-user"><div>{{ initials }}</div><span><strong>{{ displayName }}</strong><small>{{ user?.email }}</small></span></div>
          <nav>
            <button v-for="section in sections" :key="section.id" type="button" :class="{ active: activeSection === section.id }" @click="activeSection = section.id"><i class="bi" :class="section.icon"></i><span>{{ section.label }}</span><i class="bi bi-chevron-right"></i></button>
            <NuxtLink to="/wishlist"><i class="bi bi-heart"></i><span>Wishlist</span><b>2</b></NuxtLink>
            <button type="button" class="account-logout" :disabled="isLoggingOut" @click="handleLogout"><i class="bi bi-box-arrow-right"></i><span>{{ isLoggingOut ? 'Logging out...' : 'Log out' }}</span></button>
          </nav>
          <div class="account-support"><i class="bi bi-headset"></i><div><small>Need assistance?</small><strong>We are here to help</strong><NuxtLink to="/contact">Contact support</NuxtLink></div></div>
        </aside>

        <div class="account-content">
          <template v-if="activeSection === 'dashboard'">
            <div class="account-heading"><div><small>Overview</small><h2>Your account at a glance</h2></div><NuxtLink to="/shop">Continue shopping <i class="bi bi-arrow-right"></i></NuxtLink></div>
            <div class="account-summary-grid">
              <button type="button" @click="activeSection = 'orders'"><i class="bi bi-box-seam"></i><span><strong>3</strong><small>Total orders</small></span><i class="bi bi-arrow-up-right"></i></button>
              <NuxtLink to="/wishlist"><i class="bi bi-heart"></i><span><strong>2</strong><small>Wishlist items</small></span><i class="bi bi-arrow-up-right"></i></NuxtLink>
              <NuxtLink to="/cart"><i class="bi bi-cart3"></i><span><strong>3</strong><small>Cart items</small></span><i class="bi bi-arrow-up-right"></i></NuxtLink>
            </div>

            <div class="account-panel">
              <div class="account-panel-head"><div><small>Recent activity</small><h3>Recent orders</h3></div><button type="button" @click="activeSection = 'orders'">View all orders</button></div>
              <div class="account-order-table">
                <div class="order-table-head"><span>Order</span><span>Date</span><span>Status</span><span>Total</span><span></span></div>
                <div v-for="order in orders.slice(0, 2)" :key="order.id" class="account-order-row"><strong>{{ order.id }}</strong><span data-label="Date">{{ order.date }}</span><em :class="order.tone">{{ order.status }}</em><span data-label="Total">{{ order.total }} <small>{{ order.items }}</small></span><NuxtLink to="/account/order-details" aria-label="View order"><i class="bi bi-arrow-right"></i></NuxtLink></div>
              </div>
            </div>

            <div class="account-bottom-grid">
              <div class="account-panel account-address-card"><div class="account-panel-head"><div><small>Default address</small><h3>Shipping address</h3></div><button type="button" @click="activeSection = 'address'">Edit</button></div><address><strong>Sumon Rahman</strong><span>House 12, Road 4</span><span>Dhanmondi, Dhaka 1209</span><span>Bangladesh</span><span>+880 1712-345678</span></address></div>
              <div class="account-help-card"><i class="bi bi-shield-check"></i><h3>Shop with confidence</h3><p>Your account and payment information are protected.</p><NuxtLink to="/contact">Get customer support <i class="bi bi-arrow-right"></i></NuxtLink></div>
            </div>
          </template>

          <template v-else-if="activeSection === 'orders'">
            <div class="account-heading"><div><small>Order history</small><h2>My orders</h2><p>Track current purchases and review previous orders.</p></div><NuxtLink to="/shop">Shop products <i class="bi bi-arrow-right"></i></NuxtLink></div>
            <div class="account-panel account-all-orders">
              <div v-for="order in orders" :key="order.id" class="account-order-card"><div><small>Order number</small><strong>{{ order.id }}</strong></div><div><small>Placed on</small><span>{{ order.date }}</span></div><div><small>Total</small><span>{{ order.total }} Â· {{ order.items }}</span></div><em :class="order.tone">{{ order.status }}</em><NuxtLink to="/account/order-details">View details <i class="bi bi-arrow-right"></i></NuxtLink></div>
            </div>
          </template>

          <template v-else-if="activeSection === 'address'">
            <div class="account-heading"><div><small>Delivery details</small><h2>Saved addresses</h2><p>Manage where your NOVACART orders are delivered.</p></div><button class="account-add-button" type="button"><i class="bi bi-plus-lg"></i> Add address</button></div>
            <div class="address-grid"><article><span>Default</span><i class="bi bi-house"></i><h3>Home</h3><address><strong>Sumon Rahman</strong><small>House 12, Road 4<br>Dhanmondi, Dhaka 1209<br>Bangladesh<br>+880 1712-345678</small></address><div><button type="button">Edit</button><button type="button">Remove</button></div></article><button class="address-add-card" type="button"><i class="bi bi-plus-lg"></i><strong>Add a new address</strong><small>Save another delivery location</small></button></div>
          </template>

          <template v-else>
            <div class="account-heading"><div><small>Personal information</small><h2>Account details</h2><p>Keep your contact information accurate and up to date.</p></div></div>
            <form class="account-profile-form" @submit.prevent="profileSaved = true">
              <div><label><span>First name</span><input value="Sumon" autocomplete="given-name" required></label><label><span>Last name</span><input value="Rahman" autocomplete="family-name" required></label><label class="full"><span>Email address</span><input type="email" value="sumon@example.com" autocomplete="email" required></label><label class="full"><span>Phone number</span><input type="tel" value="+880 1712-345678" autocomplete="tel"></label><label class="full"><span>Current password <small>(required to change details)</small></span><input type="password" autocomplete="current-password"></label></div>
              <button type="submit">Save changes <i class="bi bi-arrow-right"></i></button>
              <p v-if="profileSaved" class="account-saved"><i class="bi bi-check-circle-fill"></i> Your account details have been saved.</p>
            </form>
          </template>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.customer-account{background:#f7f9f7}.account-banner{background:#17211d;color:#fff}.account-banner>.container{display:flex;justify-content:space-between;align-items:center;min-height:210px}.account-banner span{color:var(--brand);font-size:.7rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}.account-banner h1{margin:8px 0 5px;font-size:clamp(2rem,4vw,3.5rem)}.account-banner p{margin:0;color:#aeb9b3}.account-avatar{display:grid;width:88px;height:88px;border:1px solid rgba(255,255,255,.18);border-radius:50%;place-items:center;background:rgba(255,255,255,.06);color:var(--brand);font-size:1.6rem;font-weight:800}.account-dashboard{padding-top:45px;padding-bottom:75px}.account-layout{display:grid;grid-template-columns:260px minmax(0,1fr);gap:30px}.account-sidebar{align-self:start;border:1px solid #e1e6e2;background:#fff}.account-user{display:flex;align-items:center;gap:12px;padding:22px;border-bottom:1px solid #e8ece9}.account-user>div{display:grid;flex:0 0 44px;width:44px;height:44px;border-radius:50%;place-items:center;background:#fff0ec;color:var(--brand);font-weight:800}.account-user span,.account-user small{display:block;min-width:0}.account-user small{overflow:hidden;margin-top:2px;color:#8b9590;font-size:.67rem;text-overflow:ellipsis}.account-sidebar nav{padding:10px}.account-sidebar nav button,.account-sidebar nav>a{display:flex;width:100%;align-items:center;gap:11px;border:0;background:transparent;padding:12px;color:#68736d;text-decoration:none;font-size:.78rem;text-align:left}.account-sidebar nav button>i:first-child,.account-sidebar nav>a>i{width:20px;color:#98a19c}.account-sidebar nav button span,.account-sidebar nav>a span{flex:1}.account-sidebar nav button>i:last-child{font-size:.65rem}.account-sidebar nav button.active{background:#fff0ec;color:var(--ink);font-weight:700}.account-sidebar nav button.active>i:first-child{color:var(--brand)}.account-sidebar nav a b{display:grid;width:21px;height:21px;border-radius:50%;place-items:center;background:#f0f2f0;color:#5e6863;font-size:.62rem}.account-sidebar nav .account-logout{margin-top:7px;border-top:1px solid #edf0ee;color:#b84d42}.account-support{display:flex;gap:12px;margin:10px;padding:16px;background:#17211d;color:#fff}.account-support>i{color:var(--brand);font-size:1.25rem}.account-support small,.account-support strong{display:block}.account-support small{color:#9eaaa4;font-size:.65rem}.account-support strong{margin:2px 0 7px;font-size:.77rem}.account-support a{color:#fff;font-size:.68rem}.account-content{min-width:0}.account-heading{display:flex;justify-content:space-between;align-items:end;gap:25px;margin-bottom:25px}.account-heading small{color:var(--brand);font-weight:800;text-transform:uppercase}.account-heading h2{margin:5px 0 0;font-size:clamp(1.8rem,3vw,2.7rem)}.account-heading p{margin:6px 0 0;color:#7b8580}.account-heading>a,.account-heading>button{flex:0 0 auto;border:0;background:transparent;color:var(--ink);font-size:.75rem;font-weight:700;text-decoration:none}.account-heading>a i{margin-left:7px}.account-summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-bottom:22px}.account-summary-grid>a,.account-summary-grid>button{display:flex;align-items:center;gap:13px;min-width:0;border:1px solid #e1e6e2;background:#fff;padding:20px;color:var(--ink);text-align:left;text-decoration:none}.account-summary-grid>a>i:first-child,.account-summary-grid>button>i:first-child{display:grid;flex:0 0 43px;width:43px;height:43px;place-items:center;background:#fff0ec;color:var(--brand);font-size:1.15rem}.account-summary-grid span{flex:1}.account-summary-grid strong,.account-summary-grid small{display:block}.account-summary-grid strong{font-size:1.25rem}.account-summary-grid small{color:#8b9590;font-size:.68rem}.account-summary-grid>a>i:last-child,.account-summary-grid>button>i:last-child{color:#a1aaa5}.account-panel{border:1px solid #e1e6e2;background:#fff;padding:25px}.account-panel-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:19px}.account-panel-head small{color:#9aa39e;font-size:.65rem;text-transform:uppercase}.account-panel-head h3{margin:2px 0 0;font-size:1.15rem}.account-panel-head button{border:0;background:transparent;color:var(--brand);font-size:.72rem;font-weight:700}.order-table-head,.account-order-row{display:grid;grid-template-columns:1.1fr 1fr .9fr 1.1fr 35px;align-items:center;gap:12px}.order-table-head{padding:10px 12px;background:#f7f9f7;color:#939c97;font-size:.65rem;text-transform:uppercase}.account-order-row{padding:17px 12px;border-bottom:1px solid #edf0ee;font-size:.78rem}.account-order-row:last-child{border-bottom:0}.account-order-row em,.account-order-card em{justify-self:start;border-radius:20px;padding:5px 9px;font-size:.64rem;font-style:normal;font-weight:700}.processing{background:#fff2d8;color:#a26900}.delivered{background:#e8f7ed;color:#267647}.account-order-row span small{display:block;color:#9ba49f;font-size:.63rem}.account-order-row button{border:0;background:transparent}.account-bottom-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:20px;margin-top:20px}.account-address-card address{display:grid;gap:3px;margin:0;color:#727c77;font-size:.78rem;font-style:normal}.account-address-card address strong{margin-bottom:3px;color:var(--ink)}.account-help-card{background:#fff0ec;padding:27px}.account-help-card>i{color:var(--brand);font-size:1.8rem}.account-help-card h3{margin:12px 0 7px}.account-help-card p{color:#747f79;font-size:.78rem}.account-help-card a{color:var(--ink);font-size:.72rem;font-weight:700}.account-all-orders{padding:0}.account-order-card{display:grid;grid-template-columns:1fr 1fr 1.2fr .8fr auto;align-items:center;gap:18px;padding:22px;border-bottom:1px solid #e8ece9}.account-order-card:last-child{border:0}.account-order-card small,.account-order-card strong,.account-order-card span{display:block}.account-order-card small{margin-bottom:4px;color:#929b96;font-size:.63rem;text-transform:uppercase}.account-order-card button{border:1px solid #dfe4e1;background:#fff;padding:9px 12px;font-size:.68rem;font-weight:700}.address-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}.address-grid article,.address-add-card{min-height:285px;border:1px solid #e1e6e2;background:#fff;padding:27px}.address-grid article{position:relative}.address-grid article>span{position:absolute;top:20px;right:20px;background:#e8f7ed;padding:5px 9px;color:#267647;font-size:.62rem;font-weight:700}.address-grid article>i{color:var(--brand);font-size:1.5rem}.address-grid article h3{margin:14px 0}.address-grid address{font-style:normal}.address-grid address strong,.address-grid address small{display:block}.address-grid address small{color:#77817c;line-height:1.8}.address-grid article>div{display:flex;gap:15px;margin-top:20px}.address-grid article button{border:0;background:transparent;padding:0;color:var(--brand);font-size:.72rem;font-weight:700}.address-add-card{display:grid;align-content:center;justify-items:center;color:#89938e}.address-add-card i{display:grid;width:48px;height:48px;border:1px solid #dce1de;border-radius:50%;place-items:center;font-size:1.2rem}.address-add-card strong{margin:12px 0 4px;color:var(--ink)}.address-add-card small{font-size:.7rem}.account-profile-form{border:1px solid #e1e6e2;background:#fff;padding:30px}.account-profile-form>div{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}.account-profile-form label,.account-profile-form label>span{display:block}.account-profile-form .full{grid-column:1/-1}.account-profile-form label>span{margin-bottom:7px;font-size:.74rem;font-weight:700}.account-profile-form label small{color:#9ba49f;font-weight:400}.account-profile-form input{width:100%;border:1px solid #dce2de;background:#fbfcfb;padding:13px;outline:0}.account-profile-form input:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(255,89,65,.08)}.account-profile-form>button,.account-add-button{margin-top:22px;border:0!important;background:var(--brand)!important;padding:13px 20px!important;color:#fff!important;text-transform:uppercase}.account-saved{margin:16px 0 0;color:#267647;font-size:.78rem}.account-mobile-tabs{display:none}
@media(max-width:991px){.account-layout{grid-template-columns:1fr}.account-sidebar{display:none}.account-mobile-tabs{display:flex;overflow:auto;margin-bottom:28px;border-bottom:1px solid #dfe4e1}.account-mobile-tabs button{flex:0 0 auto;border:0;border-bottom:2px solid transparent;background:transparent;padding:12px 15px;color:#717b76;font-size:.72rem}.account-mobile-tabs button i{margin-right:6px}.account-mobile-tabs button.active{border-color:var(--brand);color:var(--ink);font-weight:700}}
@media(max-width:767px){.account-banner>.container{min-height:175px}.account-avatar{width:65px;height:65px}.account-dashboard{padding-top:30px}.account-summary-grid{grid-template-columns:1fr}.account-bottom-grid{grid-template-columns:1fr}.order-table-head{display:none}.account-order-row{grid-template-columns:1fr 1fr}.account-order-row em{justify-self:end}.account-order-row span:before{display:block;color:#9ba49f;font-size:.6rem;content:attr(data-label);text-transform:uppercase}.account-order-row button{display:none}.account-order-card{grid-template-columns:1fr 1fr}.account-order-card em{justify-self:end}.account-order-card button{grid-column:1/-1}.address-grid{grid-template-columns:1fr}.account-heading{align-items:flex-start;flex-direction:column}.account-profile-form>div{grid-template-columns:1fr}.account-profile-form .full{grid-column:auto}}
@media(max-width:480px){.account-banner p{max-width:240px;font-size:.78rem}.account-avatar{display:none}.account-summary-grid>a,.account-summary-grid>button{padding:16px}.account-panel{padding:18px}.account-order-row{padding:15px 4px}.account-order-card{grid-template-columns:1fr}.account-order-card em{justify-self:start}.account-profile-form{padding:20px}}
.account-order-row>a{color:var(--ink);text-align:center}.account-order-card>a{border:1px solid #dfe4e1;background:#fff;padding:9px 12px;color:var(--ink);font-size:.68rem;font-weight:700;text-align:center;text-decoration:none}@media(max-width:767px){.account-order-row>a{display:none}.account-order-card>a{grid-column:1/-1}}
</style>

