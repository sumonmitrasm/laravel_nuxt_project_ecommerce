<script setup>
const config = useRuntimeConfig()

const form = reactive({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    topic: '',
    message: '',
})

const consent = ref(false)
const sending = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const { data: contact } = await useFetch('/contact', {
    baseURL: config.public.apiBase,
})

const companyLocation = computed(() => contact.value?.address || 'Dhaka, Bangladesh')
const mapUrl = computed(() => `https://www.google.com/maps?q=${encodeURIComponent(companyLocation.value)}&output=embed`)
const directionsUrl = computed(() => contact.value?.map_url || `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(companyLocation.value)}`)

const cleanFirstName = () => { form.first_name = form.first_name.replace(/[^\p{L}\s.-]/gu, '') }
const cleanLastName = () => { form.last_name = form.last_name.replace(/[^\p{L}\s.-]/gu, '') }
const cleanPhone = () => { form.phone = form.phone.replace(/[^0-9+\-\s()]/g, '') }
const cleanMessage = () => { form.message = form.message.replace(/[^\p{L}\p{N}\s.,!?()'"\-]/gu, '') }

const submitContact = async () => {
    successMessage.value = ''
    errorMessage.value = ''

    if (!consent.value) {
        errorMessage.value = 'Please accept the consent checkbox.'
        return
    }

    try {
        sending.value = true
        const response = await $fetch('/contact', {
            baseURL: config.public.apiBase,
            method: 'POST',
            body: form,
        })

        successMessage.value = response.message
        Object.assign(form, { first_name: '', last_name: '', email: '', phone: '', topic: '', message: '' })
        consent.value = false
    } catch (error) {
        const errors = error?.data?.errors
        errorMessage.value = errors ? Object.values(errors).flat().join(' ') : error?.data?.message || 'Message could not be sent.'
    } finally {
        sending.value = false
    }
}

useHead(() => ({
    title: 'Contact Us | ' + (contact.value?.site_name || 'Website'),
    meta: [{ name: 'description', content: 'Contact ' + (contact.value?.site_name || 'our') + ' customer support.' }],
}))
</script>

<template>
    <main>
        <section class="shop-hero contact-hero">
            <div class="container text-center">
                <small class="contact-eyebrow">We are here to help</small>
                <h1>Contact Us</h1>
                <p>Questions about an order or a product? Our team would love to help.</p>
            </div>
        </section>

        <div class="shop-breadcrumb">
            <div class="container">
                <NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><span>Contact Us</span>
            </div>
        </div>

        <section class="contact-section">
            <div class="container">
                <div class="contact-cards">
                    <a class="contact-card" :href="contact?.phone ? 'tel:' + contact.phone : '#'">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <small>Call us</small><strong>{{ contact?.phone || 'Not available' }}</strong>
                            <p>Sat&ndash;Thu, 9:00 AM&ndash;8:00 PM</p>
                        </div>
                    </a>
                    <a class="contact-card" :href="contact?.email ? 'mailto:' + contact.email : '#'">
                        <i class="bi bi-envelope"></i>
                        <div>
                            <small>Email us</small><strong>{{ contact?.email || 'Not available' }}</strong>
                            <p>We usually reply within 24 hours</p>
                        </div>
                    </a>
                    <div class="contact-card">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <small>Visit us</small><strong>{{ contact?.address || 'Dhaka, Bangladesh' }}</strong>
                            <p>{{ contact?.site_name || 'Our Store' }} Centre</p>
                        </div>
                    </div>
                </div>

                <div class="contact-shell">
                    <div class="contact-copy">
                        <small class="contact-eyebrow">Customer support</small>
                        <h2>How can we help?</h2>
                        <p>
                            Send us a message with as much information as possible. For order questions, please include
                            your order number.
                        </p>
                        <div class="contact-help-list">
                            <div>
                                <i class="bi bi-box-seam"></i
                                ><span
                                    ><strong>Orders &amp; delivery</strong
                                    ><small>Track an order or get delivery help</small></span
                                >
                            </div>
                            <div>
                                <i class="bi bi-arrow-counterclockwise"></i
                                ><span
                                    ><strong>Returns &amp; refunds</strong
                                    ><small>Start a return or check refund status</small></span
                                >
                            </div>
                            <div>
                                <i class="bi bi-shield-check"></i
                                ><span
                                    ><strong>Product support</strong
                                    ><small>Warranty and product information</small></span
                                >
                            </div>
                        </div>
                        <div class="contact-note">
                            <i class="bi bi-chat-dots"></i
                            ><span
                                ><strong>Need a quick answer?</strong
                                ><small>Our support team is ready to assist you.</small></span
                            >
                        </div>
                    </div>

                    <div class="contact-form-panel">
                        <form @submit.prevent="submitContact">
                            <div class="contact-form-head">
                                <small>Send a message</small>
                                <h2>Tell us what you need</h2>
                            </div>
                            <div class="contact-fields">
                                <label
                                    ><span>First name</span><input v-model="form.first_name" name="first_name" autocomplete="given-name" minlength="2" maxlength="50" required @input="cleanFirstName" /></label>
                                <label
                                    ><span>Last name</span><input v-model="form.last_name" name="last_name" autocomplete="family-name" minlength="2" maxlength="50" required @input="cleanLastName" /></label>
                                <label class="full"
                                    ><span>Email address</span
                                    ><input
                                        v-model="form.email"
                                        name="email"
                                        type="email"
                                        autocomplete="email"
                                        placeholder="you@example.com"
                                        required
                                /></label>
                                <label class="full"
                                    ><span>Phone <small>(optional)</small></span
                                    ><input v-model="form.phone" name="phone" type="tel" maxlength="20" autocomplete="tel" placeholder="+880 1XXX-XXXXXX" @input="cleanPhone"
                                /></label>
                                <label class="full"
                                    ><span>Topic</span
                                    ><select v-model="form.topic" name="topic" required>
                                        <option value="" disabled>Select a topic</option>
                                        <option>Order and delivery</option>
                                        <option>Returns and refunds</option>
                                        <option>Product information</option>
                                        <option>Payment issue</option>
                                        <option>Other</option>
                                    </select></label
                                >
                                <label class="full"
                                    ><span>Message</span
                                    ><textarea
                                        v-model="form.message"
                                        name="message"
                                        minlength="10"
                                        maxlength="2000"
                                        @input="cleanMessage"
                                        rows="5"
                                        placeholder="How can we help you?"
                                        required
                                    ></textarea>
                                </label>
                            </div>
                            <label class="contact-consent"
                                ><input v-model="consent" type="checkbox" required /><span
                                    >I agree that {{ contact?.site_name || 'this website' }} may use my details to respond to this enquiry.</span
                                ></label
                            >
                            <div v-if="errorMessage" class="contact-error">{{ errorMessage }}</div>
                            <button class="contact-submit" type="submit" :disabled="sending">
                                {{ sending ? 'Sending...' : 'Send message' }} <i class="bi bi-arrow-right"></i>
                            </button>
                            <div v-if="successMessage" class="contact-success" role="status">
                                <i class="bi bi-check-circle-fill"></i
                                ><span
                                    ><strong>{{ successMessage }}</strong></span
                                >
                            </div>
                        </form>
                    </div>
                </div>

                <div class="contact-map">
                    <div class="contact-map-head">
                        <div>
                            <small class="contact-eyebrow">Find our store</small>
                            <h2>Visit {{ contact?.site_name || 'Our Store' }}</h2>
                            <p><i class="bi bi-geo-alt"></i> {{ companyLocation }}</p>
                        </div>
                        <a :href="directionsUrl" target="_blank" rel="noopener noreferrer"
                            >Get directions <i class="bi bi-arrow-up-right"></i
                        ></a>
                    </div>
                    <div class="contact-map-frame">
                        <iframe
                            :src="mapUrl"
                            :title="(contact?.site_name || 'Store') + ' location on Google Maps'"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-strip">
            <div class="container">
                <div><i class="bi bi-truck"></i><strong>Fast delivery support</strong></div>
                <div><i class="bi bi-arrow-repeat"></i><strong>Easy return guidance</strong></div>
                <div><i class="bi bi-lock"></i><strong>Secure customer care</strong></div>
            </div>
        </section>
    </main>
</template>

<style scoped>
    .contact-hero {
        padding: 76px 0 70px;
        background: radial-gradient(circle at 75% 25%, rgba(255, 89, 65, 0.13), transparent 28%),
            linear-gradient(135deg, #f3f7f3, #fff8f4);
    }
    .contact-hero h1 {
        margin: 8px 0 10px;
    }
    .contact-hero p {
        max-width: 620px;
        margin: auto;
        color: #68736d;
    }
    .contact-eyebrow {
        color: var(--brand);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }
    .contact-section {
        padding: 70px 0 80px;
    }
    .contact-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }
    .contact-card {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        padding: 22px;
        border: 1px solid #e3e8e5;
        color: var(--ink);
        text-decoration: none;
        transition: 0.25s;
    }
    .contact-card[href]:hover {
        color: var(--ink);
        border-color: rgba(255, 89, 65, 0.5);
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(23, 33, 29, 0.08);
    }
    .contact-card > i {
        display: grid;
        flex: 0 0 52px;
        width: 52px;
        height: 52px;
        place-items: center;
        background: #fff0ec;
        color: var(--brand);
        font-size: 1.25rem;
    }
    .contact-card div {
        min-width: 0;
    }
    .contact-card small,
    .contact-card strong,
    .contact-card p {
        display: block;
    }
    .contact-card small {
        margin-bottom: 3px;
        color: #929b96;
        font-size: 0.68rem;
        text-transform: uppercase;
    }
    .contact-card strong {
        overflow-wrap: anywhere;
    }
    .contact-card p {
        margin: 3px 0 0;
        color: #79827d;
        font-size: 0.78rem;
    }
    .contact-shell {
        display: grid;
        grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr);
        overflow: hidden;
        border: 1px solid #e3e8e5;
        box-shadow: 0 22px 55px rgba(23, 33, 29, 0.08);
    }
    .contact-copy {
        padding: 54px 46px;
        background: #17211d;
        color: #fff;
    }
    .contact-copy h2 {
        margin: 10px 0 14px;
        font-size: clamp(2rem, 3vw, 3rem);
    }
    .contact-copy > p {
        color: #b9c3be;
        line-height: 1.8;
    }
    .contact-help-list {
        display: grid;
        gap: 22px;
        margin: 38px 0;
    }
    .contact-help-list > div,
    .contact-note {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .contact-help-list i,
    .contact-note > i {
        display: grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: var(--brand);
    }
    .contact-help-list span,
    .contact-help-list small,
    .contact-note span,
    .contact-note small {
        display: block;
    }
    .contact-help-list small,
    .contact-note small {
        margin-top: 3px;
        color: #9eaaa4;
    }
    .contact-note {
        padding: 18px;
        background: rgba(255, 255, 255, 0.06);
    }
    .contact-form-panel {
        padding: 52px;
    }
    .contact-form-head small {
        color: var(--brand);
        font-weight: 800;
        text-transform: uppercase;
    }
    .contact-form-head h2 {
        margin: 6px 0 28px;
    }
    .contact-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .contact-fields label,
    .contact-fields label > span {
        display: block;
    }
    .contact-fields .full {
        grid-column: 1/-1;
    }
    .contact-fields label > span {
        margin-bottom: 7px;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .contact-fields label > span small {
        color: #a0a8a4;
        font-weight: 400;
    }
    .contact-fields input,
    .contact-fields select,
    .contact-fields textarea {
        width: 100%;
        border: 1px solid #dce2de;
        border-radius: 0;
        background: #fbfcfb;
        padding: 13px 14px;
        color: var(--ink);
        outline: 0;
        transition: 0.2s;
    }
    .contact-fields textarea {
        min-height: 125px;
        resize: vertical;
    }
    .contact-fields input:focus,
    .contact-fields select:focus,
    .contact-fields textarea:focus {
        border-color: var(--brand);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(255, 89, 65, 0.09);
    }
    .contact-consent {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 20px 0;
        color: #6f7974;
        font-size: 0.76rem;
    }
    .contact-consent input {
        margin-top: 3px;
        accent-color: var(--brand);
    }
    .contact-submit {
        border: 0;
        background: var(--brand);
        padding: 14px 25px;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .contact-submit i {
        margin-left: 8px;
    }
    .contact-error { margin: 0 0 15px; padding: 12px 15px; background: #fff0ee; color: #b73527; font-size: 0.8rem; }
    .contact-success {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 18px;
        padding: 14px 16px;
        background: #eaf8ef;
        color: #207442;
        font-size: 0.8rem;
    }
    .contact-strip {
        border-top: 1px solid #e3e8e5;
        background: #f7f9f7;
    }
    .contact-strip .container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }
    .contact-strip .container > div {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        padding: 25px;
        border-right: 1px solid #e3e8e5;
    }
    .contact-strip .container > div:first-child {
        border-left: 1px solid #e3e8e5;
    }
    .contact-strip i {
        color: var(--brand);
        font-size: 1.2rem;
    }
    .contact-map {
        margin-top: 30px;
        border: 1px solid #e3e8e5;
        background: #fff;
    }
    .contact-map-head {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 25px;
        padding: 28px 30px;
    }
    .contact-map-head h2 {
        margin: 6px 0;
    }
    .contact-map-head p {
        margin: 0;
        color: #77817c;
    }
    .contact-map-head p i {
        margin-right: 5px;
        color: var(--brand);
    }
    .contact-map-head > a {
        flex: 0 0 auto;
        background: var(--brand);
        padding: 13px 20px;
        color: #fff;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .contact-map-head > a i {
        margin-left: 7px;
    }
    .contact-map-frame {
        height: 420px;
        overflow: hidden;
        background: #edf0ee;
    }
    .contact-map-frame iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }
    @media (max-width: 991px) {
        .contact-cards,
        .contact-shell {
            grid-template-columns: 1fr;
        }
        .contact-copy,
        .contact-form-panel {
            padding: 42px;
        }
    }
    @media (max-width: 575px) {
        .contact-hero {
            padding: 52px 0;
        }
        .contact-section {
            padding: 42px 0 52px;
        }
        .contact-card {
            padding: 17px;
        }
        .contact-copy,
        .contact-form-panel {
            padding: 30px 22px;
        }
        .contact-fields {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .contact-fields .full {
            grid-column: auto;
        }
        .contact-submit {
            width: 100%;
        }
        .contact-map-head {
            display: block;
            padding: 24px 20px;
        }
        .contact-map-head > a {
            display: inline-block;
            margin-top: 18px;
        }
        .contact-map-frame {
            height: 330px;
        }
        .contact-strip .container {
            grid-template-columns: 1fr;
        }
        .contact-strip .container > div,
        .contact-strip .container > div:first-child {
            justify-content: flex-start;
            border-right: 0;
            border-left: 0;
            border-bottom: 1px solid #e3e8e5;
        }
    }
</style>

