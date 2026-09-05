<script setup lang="ts">
import type { PageSeoData } from '~/composables/usePageSeo'
type AttributeValue = {
    id: number
    attribute_id: number
    value: string
    color_code: string | null
    attribute: { id: number; name: string; slug: string; type: string }
}

type ProductVariant = {
    id: number
    sku: string
    stock: number
    regular_price: string
    final_price: string
    values: AttributeValue[]
}

type ProductDetailResponse = {
    status: boolean
    seo: PageSeoData
    product: Record<string, any> & {
        variants?: ProductVariant[]
    }
}

const route = useRoute()
const config = useRuntimeConfig()
const { addToCart } = useCart()
const productPage = ref<HTMLElement | null>(null)
const interactionCleanups: Array<() => void> = []
const selectedValues = reactive<Record<number, number>>({})
const quantity = ref(1)
const activeImage = ref('')
const thumbnailStart = ref(0)
const cartMessage = ref('')
const cartError = ref('')
const addingToCart = ref(false)

const productId = computed(() => {
    const value = Array.isArray(route.query.id) ? route.query.id[0] : route.query.id
    const id = Number.parseInt(value?.toString() ?? '', 10)
    return Number.isInteger(id) && id > 0 ? id : null
})

const { data, status, error } = await useAsyncData<ProductDetailResponse>(
    () => `product-detail-${productId.value ?? 'invalid'}`,
    () => {
        if (!productId.value) throw createError({ statusCode: 404, statusMessage: 'Product not found.' })
        return $fetch<ProductDetailResponse>(`/detail/${productId.value}`, { baseURL: config.public.apiBase })
    },
    { watch: [productId] }
)

const product = computed<any>(() => data.value?.product ?? null)
const pageSeo = computed(() => data.value?.seo)

usePageSeo(pageSeo)
const variants = computed<ProductVariant[]>(() => product.value?.variants ?? [])
const hasVariants = computed(() => variants.value.length > 0)

const attributeGroups = computed(() => {
    const groups = new Map<number, { id: number; name: string; slug: string; type: string; values: AttributeValue[] }>()

    variants.value.forEach(variant => variant.values.forEach(value => {
        if (!groups.has(value.attribute_id)) groups.set(value.attribute_id, { ...value.attribute, values: [] })
        const group = groups.get(value.attribute_id)!
        if (!group.values.some(option => option.id === value.id)) group.values.push(value)
    }))

    return [...groups.values()]
})

const selectedVariant = computed(() => {
    if (!hasVariants.value || attributeGroups.value.some(group => !selectedValues[group.id])) return null
    return variants.value.find(variant => attributeGroups.value.every(group =>
        variant.values.some(value => value.attribute_id === group.id && value.id === selectedValues[group.id])
    )) ?? null
})

const optionAvailable = (attributeId: number, valueId: number) => variants.value.some(variant => {
    if (variant.stock < 1) return false
    const includesCandidate = variant.values.some(value => value.attribute_id === attributeId && value.id === valueId)
    const matchesOtherSelections = Object.entries(selectedValues).every(([selectedAttributeId, selectedValueId]) =>
        Number(selectedAttributeId) === attributeId ||
        variant.values.some(value => value.attribute_id === Number(selectedAttributeId) && value.id === selectedValueId)
    )
    return includesCandidate && matchesOtherSelections
})

const selectOption = (attributeId: number, valueId: number) => {
    if (!optionAvailable(attributeId, valueId)) return
    selectedValues[attributeId] = valueId
    quantity.value = 1
    cartError.value = ''
    cartMessage.value = ''
}

const maximumQuantity = computed(() => Math.min(selectedVariant.value?.stock ?? 3, 3))
const productAvailable = computed(() => !hasVariants.value || variants.value.some(variant => variant.stock > 0))
const displayRegularPrice = computed(() => selectedVariant.value?.regular_price ?? product.value?.product_price ?? 0)
const displayFinalPrice = computed(() => selectedVariant.value?.final_price ?? product.value?.final_price ?? 0)
const displaySku = computed(() => selectedVariant.value?.sku ?? product.value?.product_code ?? '')

const formatPrice = (value: unknown) => new Intl.NumberFormat('en-BD', { maximumFractionDigits: 2 }).format(Number(value) || 0)
const discountText = computed(() => {
    const discount = Number(product.value?.effective_discount) || 0
    return discount > 0 ? `Save ${formatPrice(discount)}%` : ''
})

const galleryImages = computed(() => {
    if (!product.value) return []
    const images = [product.value.image_url, ...(product.value.images ?? []).map((image: any) => image.url)]
    return [...new Set<string>(images.filter(Boolean))]
})

const visibleThumbnailCount = 5
const visibleGalleryImages = computed(() =>
    galleryImages.value.slice(thumbnailStart.value, thumbnailStart.value + visibleThumbnailCount)
)
const canShowPreviousThumbnails = computed(() => thumbnailStart.value > 0)
const canShowNextThumbnails = computed(() => thumbnailStart.value + visibleThumbnailCount < galleryImages.value.length)

const moveThumbnails = (direction: number) => {
    const lastStart = Math.max(0, galleryImages.value.length - visibleThumbnailCount)
    thumbnailStart.value = Math.min(lastStart, Math.max(0, thumbnailStart.value + direction))
}

const selectGalleryImage = (image: string) => {
    activeImage.value = image
    const index = galleryImages.value.indexOf(image)
    if (index < thumbnailStart.value) thumbnailStart.value = index
    if (index >= thumbnailStart.value + visibleThumbnailCount) thumbnailStart.value = index - visibleThumbnailCount + 1
}

watch(galleryImages, images => {
    if (!images.includes(activeImage.value)) {
        activeImage.value = images[0] ?? ''
        thumbnailStart.value = 0
    }
}, { immediate: true })

const changeQuantity = (amount: number) => {
    quantity.value = Math.min(maximumQuantity.value, Math.max(1, quantity.value + amount))
}

const normalizeQuantity = () => {
    const value = Number.isFinite(quantity.value) ? Math.trunc(quantity.value) : 1
    quantity.value = Math.min(maximumQuantity.value, Math.max(1, value))
}

const validateSelection = () => {
    if (!hasVariants.value) return true
    const missing = attributeGroups.value.find(group => !selectedValues[group.id])
    if (missing) {
        cartError.value = `Please select ${missing.name}.`
        return false
    }
    if (!selectedVariant.value || selectedVariant.value.stock < 1) {
        cartError.value = 'This option combination is out of stock.'
        return false
    }
    return true
}

const submitCart = async (buyNow = false) => {
    cartMessage.value = ''
    cartError.value = ''
    if (!product.value || !validateSelection()) return

    try {
        addingToCart.value = true
        const response = await addToCart({
            product_id: product.value.id,
            product_variant_id: selectedVariant.value?.id ?? null,
            quantity: quantity.value
        })
        cartMessage.value = response.message ?? 'Product added to cart.'
        if (buyNow) await navigateTo('/checkout')
    } catch (requestError: any) {
        const errors = requestError?.data?.errors
        cartError.value = errors ? Object.values(errors).flat().join(' ') : requestError?.data?.message ?? 'Product could not be added.'
    } finally {
        addingToCart.value = false
    }
}

onBeforeUnmount(() => interactionCleanups.splice(0).forEach(cleanup => cleanup()))

onMounted(() => {
    const page = productPage.value
    if (!page) return
    const mainImage = page.querySelector<HTMLImageElement>('[data-product-main]')
    const zoomContainer = page.querySelector<HTMLElement>('[data-zoom-container]')
    const modal = document.querySelector<HTMLElement>('[data-zoom-modal]')

    const listen = (element: EventTarget | null | undefined, event: string, handler: EventListener) => {
        if (!element) return
        element.addEventListener(event, handler)
        interactionCleanups.push(() => element.removeEventListener(event, handler))
    }

    listen(zoomContainer, 'mousemove', ((event: MouseEvent) => {
        if (!mainImage || !zoomContainer || !window.matchMedia('(hover: hover)').matches) return
        const rect = zoomContainer.getBoundingClientRect()
        mainImage.style.transformOrigin = `${((event.clientX - rect.left) / rect.width) * 100}% ${((event.clientY - rect.top) / rect.height) * 100}%`
        mainImage.style.transform = 'scale(1.8)'
    }) as EventListener)
    listen(zoomContainer, 'mouseleave', () => { if (mainImage) mainImage.style.transform = 'scale(1)' })
    listen(page.querySelector('[data-open-zoom]'), 'click', () => modal?.classList.add('show'))
    listen(modal?.querySelector('[data-close-zoom]'), 'click', () => modal?.classList.remove('show'))
})
</script>

<template>
    <div ref="productPage">
        <main>
            <div class="product-breadcrumb">
                <div class="container"><NuxtLink to="/">Home</NuxtLink><i class="bi bi-chevron-right"></i><NuxtLink to="/shop">Products</NuxtLink><i class="bi bi-chevron-right"></i><span>{{ product?.product_name ?? 'Product' }}</span></div>
            </div>

            <section v-if="status === 'pending'" class="container py-5">Loading product...</section>
            <section v-else-if="error || !product" class="container py-5">Product could not be loaded.</section>
            <section v-else class="product-detail container py-4">
                <div class="row g-4 g-xl-5">
                    <div class="col-lg-6">
                        <div class="product-gallery">
                            <div class="product-thumbs" role="tablist">
                                <button v-if="galleryImages.length > visibleThumbnailCount" class="product-thumb-nav previous" type="button" :disabled="!canShowPreviousThumbnails" aria-label="Show previous product images" @click="moveThumbnails(-1)"><i class="bi bi-chevron-up"></i></button>
                                <div class="product-thumb-list">
                                    <button v-for="image in visibleGalleryImages" :key="image" type="button" :class="{ active: activeImage === image }" @click="selectGalleryImage(image)"><img :src="image" :alt="product.product_name"></button>
                                </div>
                                <button v-if="galleryImages.length > visibleThumbnailCount" class="product-thumb-nav next" type="button" :disabled="!canShowNextThumbnails" aria-label="Show next product images" @click="moveThumbnails(1)"><i class="bi bi-chevron-down"></i></button>
                            </div>
                            <div class="product-main-image" data-zoom-container>
                                <img :src="activeImage" :alt="product.product_name" data-product-main>
                                <button type="button" data-open-zoom aria-label="Open image zoom"><i class="bi bi-arrows-fullscreen"></i></button>
                                <span class="zoom-hint"><i class="bi bi-search"></i> Hover to zoom</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="product-summary">
                            <div class="product-stock"><span></span>{{ hasVariants && !variants.some(variant => variant.stock > 0) ? 'Out of stock' : 'In stock · Ready to ship' }}</div>
                            <h1>{{ product.product_name }}</h1>
                            <div class="product-review"><span>★★★★★</span><i></i><span class="sku">SKU: {{ displaySku }}</span></div>
                            <div class="product-detail-price"><small v-if="hasVariants && !selectedVariant">From</small> ৳{{ formatPrice(displayFinalPrice) }}<del v-if="Number(product.effective_discount) > 0">৳{{ formatPrice(displayRegularPrice) }}</del><em v-if="discountText">{{ discountText }}</em></div>
                            <p class="product-intro">{{ product.description || 'Product details and available options are shown below.' }}</p>
                            <div class="product-benefits"><span><i class="bi bi-truck"></i> Free delivery</span><span><i class="bi bi-shield-check"></i> Secure purchase</span><span><i class="bi bi-arrow-counterclockwise"></i> Easy returns</span></div>

                            <div v-for="group in attributeGroups" :key="group.id" class="product-option">
                                <div><strong>{{ group.name }}:</strong><span data-selected-color>{{ group.values.find(value => value.id === selectedValues[group.id])?.value ?? 'Select' }}</span></div>
                                <div v-if="group.type?.toLowerCase() === 'color'" class="color-swatches">
                                    <button v-for="option in group.values" :key="option.id" type="button" :class="{ active: selectedValues[group.id] === option.id, unavailable: !optionAvailable(group.id, option.id) }" :disabled="!optionAvailable(group.id, option.id)" :style="{ '--swatch': option.color_code || '#ddd' }" :aria-label="option.value" @click="selectOption(group.id, option.id)"></button>
                                </div>
                                <div v-else class="connection-options">
                                    <button v-for="option in group.values" :key="option.id" type="button" :class="{ active: selectedValues[group.id] === option.id, unavailable: !optionAvailable(group.id, option.id) }" :disabled="!optionAvailable(group.id, option.id)" @click="selectOption(group.id, option.id)">{{ option.value }}</button>
                                </div>
                            </div>

                            <p v-if="cartError" class="product-cart-feedback error">{{ cartError }}</p>
                            <p v-if="cartMessage" class="product-cart-feedback success">{{ cartMessage }}</p>
                            <div class="product-purchase-row">
                                <div class="product-qty qty"><button type="button" :disabled="quantity <= 1" @click="changeQuantity(-1)">−</button><input v-model.number="quantity" type="number" min="1" :max="maximumQuantity" aria-label="Quantity" @change="normalizeQuantity" @blur="normalizeQuantity"><button type="button" :disabled="quantity >= maximumQuantity" @click="changeQuantity(1)">+</button></div>
                                <button class="product-add-cart" type="button" :disabled="addingToCart || !productAvailable" @click="submitCart(false)"><i class="bi bi-cart3"></i> Add to cart</button>
                                <button class="product-action" type="button" aria-label="Add to wishlist"><i class="bi bi-heart"></i></button>
                            </div>
                            <button class="buy-now" type="button" :disabled="addingToCart || !productAvailable" @click="submitCart(true)">Buy it now</button>
                            <div class="product-meta"><span><strong>Category:</strong> {{ product.category?.category_name }}</span><span><strong>Brand:</strong> {{ product.brand?.name ?? 'No Brand' }}</span></div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="product" class="product-tabs container pb-5" id="productDetails">
                <ul class="nav"><li><button class="active">Description &amp; Specifications</button></li></ul>
                <div class="tab-content">
                    <div><h2>{{ product.product_name }}</h2><p>{{ product.description || 'No description available.' }}</p></div>
                    <table v-if="product.specifications?.length" class="mt-3">
                        <tbody><tr v-for="specification in product.specifications" :key="specification.id"><th>{{ specification.name }}</th><td>{{ specification.values.join(', ') }}</td></tr></tbody>
                    </table>
                </div>
            </section>
        </main>

        <div class="product-zoom-modal" data-zoom-modal aria-hidden="true"><button type="button" data-close-zoom aria-label="Close"><i class="bi bi-x-lg"></i></button><img :src="activeImage" :alt="product?.product_name ?? 'Product'"><span>Inspect product image</span></div>
    </div>
</template>
