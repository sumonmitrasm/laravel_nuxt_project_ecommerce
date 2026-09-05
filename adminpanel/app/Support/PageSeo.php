<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PageSeo
{
    public function home(): array
    {
        return $this->build();
    }

    public function shop(): array
    {
        return $this->build(canonical: $this->frontendUrl('/shop'));
    }

    public function category(Category $category): array
    {
        return $this->build(
            title: $category->meta_title ?: $category->category_name,
            description: $category->meta_description ?: $category->description,
            keywords: $category->meta_keywords,
            robots: $category->meta_robot,
            canonical: $this->frontendUrl('/shop?category='.rawurlencode((string) $category->url)),
            image: $category->image_url,
            schema: $category->schema_markup,
        );
    }

    public function product(Product $product): array
    {
        $productUrl = $this->frontendUrl('/product?id='.$product->id);

        return $this->build(
            title: $product->meta_title ?: $product->product_name,
            description: $product->meta_description ?: $product->description,
            keywords: $product->meta_keywords,
            robots: $product->meta_robot,
            canonical: $this->canonicalUrl($product->canonical_tag, $productUrl),
            image: $this->productImage($product),
            schema: $product->schema_markup,
            type: 'product',
        );
    }

    private function build(
        ?string $title = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $robots = null,
        ?string $canonical = null,
        ?string $image = null,
        ?string $schema = null,
        string $type = 'website',
    ): array {
        $setting = $this->setting();
        $siteName = trim((string) (($setting['side_name'] ?? null) ?: 'NovaCart'));
        $resolvedTitle = trim((string) ($title ?: ($setting['meta_title'] ?? null) ?: $siteName));

        if ($title && $siteName && ! Str::contains(Str::lower($resolvedTitle), Str::lower($siteName))) {
            $resolvedTitle .= ' | '.$siteName;
        }

        return [
            'title' => $resolvedTitle,
            'description' => $this->plainText($description ?: ($setting['meta_description'] ?? null) ?: ($setting['description'] ?? null)),
            'keywords' => $keywords ?: ($setting['meta_keywords'] ?? null),
            'robots' => $this->robots($robots)
                ?: $this->robots($setting['meta_robot'] ?? null)
                ?: 'index, follow',
            'canonical' => $this->canonicalUrl(
                $canonical ?: ($setting['canonical_tag'] ?? null),
                $this->frontendUrl(),
            ),
            'image' => $image ?: $this->settingImage($setting),
            'favicon' => $this->settingFavicon($setting),
            'type' => $type,
            'schema' => $schema ?: ($setting['schema_markup'] ?? null),
        ];
    }

    private function setting(): ?array
    {
        return Cache::remember(
            'api.general-setting.seo.v1',
            now()->addHours(6),
            fn () => Setting::query()->where('status', true)->latest('id')->first()?->toArray(),
        );
    }

    private function frontendUrl(string $path = ''): string
    {
        return rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/').$path;
    }

    private function canonicalUrl(?string $candidate, string $fallback): string
    {
        if (! $candidate || ! filter_var($candidate, FILTER_VALIDATE_URL)) {
            return $fallback;
        }

        $frontendHost = parse_url($this->frontendUrl(), PHP_URL_HOST);
        $candidateHost = parse_url($candidate, PHP_URL_HOST);

        return $frontendHost && $candidateHost === $frontendHost ? $candidate : $fallback;
    }

    private function robots(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $normalized = Str::of($value)->lower()->replaceMatches('/\s+/', ' ')->trim()->toString();

        return preg_match('/^(index|noindex)\s*,\s*(follow|nofollow)$/', $normalized)
            ? preg_replace('/\s*,\s*/', ', ', $normalized)
            : null;
    }

    private function settingImage(?array $setting): ?string
    {
        $filename = ($setting['meta_image'] ?? null) ?: ($setting['image'] ?? null);

        return $filename ? asset('admin/site_settings/'.basename($filename)) : null;
    }

    private function productImage(Product $product): ?string
    {
        if ($product->meta_image) {
            return asset('admin/productimage/'.basename($product->meta_image));
        }

        return $product->image_url;
    }

    private function plainText(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Str::of($value)->stripTags()->squish()->limit(200)->toString();
    }

    private function settingFavicon(?array $setting): ?string
    {
        $filename = $setting['favicon'] ?? null;

        return $filename ? asset('admin/site_settings/'.basename($filename)): null;
    }
}
