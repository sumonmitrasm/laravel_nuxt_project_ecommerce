import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { createHash } from 'node:crypto'

// Documentation generator only. Does not connect to the database or read .env.
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const escape = value => String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;')
const code = (value, label = 'উদাহরণ') => `<div class="codebox"><div class="codebar"><span>${escape(label)}</span><button type="button" class="copy">Copy code</button></div><pre><code>${escape(value)}</code></pre></div>`
const rows = values => `<div class="table-wrap"><table><tbody>${values.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`
const sections = []
function section(id, title, body) { sections.push({ id, title, body }) }
const files = [
  ['adminpanel/app/Support/ContentCache.php', 'মূল cache engine', 'remember() দিয়ে read; forget() দিয়ে group invalidate; version comparison ও cache-only fallback।'],
  ['adminpanel/database/migrations/2026_10_04_000001_create_content_cache_versions_table.php', 'নতুন version table', 'এটি আগে migrate করতে হবে। group primary key, version UUID।'],
  ['adminpanel/app/Support/SiteSettings.php', 'Settings-এর একমাত্র shared reader', 'active/latest Setting-কে array অথবা null করে cache রাখে।'],
  ['adminpanel/app/Observers/ContentCacheObserver.php', 'Model event listener', 'save/delete/restore-এর পরে কোন group clear হবে তা নির্ধারণ করে।'],
  ['adminpanel/app/Providers/AppServiceProvider.php', 'Observer registration ও Blade wiring', 'boot()-এ model observer attach; view composer-এ SiteSettings পাঠানো হয়। অন্য project-এ পুরো provider overwrite করবেন না।'],
  ['adminpanel/bootstrap/providers.php', 'Provider চালু হওয়ার জায়গা', 'এই project-এ AppServiceProvider এখানে registered।'],
  ['adminpanel/app/Http/Controllers/Api/FrontController.php', 'Menu, slider, about ও filter reads', 'menu(), about(), availablePriceRange(), availableAttributeFilters(), availableBrands() cache ব্যবহার করে। Product listing নিজে live।'],
  ['adminpanel/app/Http/Controllers/Api/LocationController.php', 'Location list reads', 'divisions(), districts(), upazilas() → locations group।'],
  ['adminpanel/app/Http/Controllers/Api/ShippingMethodController.php', 'Shipping list read', 'index() → shipping group।'],
  ['adminpanel/app/Http/Controllers/Api/ContactController.php', 'Contact read', 'info() → SiteSettings::get(); object conversion ছাড়াই array access।'],
  ['adminpanel/app/Support/PageSeo.php', 'SEO ও public site fields', 'setting() → SiteSettings::get(); site()/home()/অন্যান্য SEO output-এ ব্যবহার।'],
  ['adminpanel/app/Notifications/StorefrontResetPasswordNotification.php', 'Reset email branding', 'SiteSettings থেকে name/logo; আগের object বনাম array format mismatch দূর হয়েছে।'],
  ['adminpanel/app/Http/Controllers/CategoryController.php', 'Category pivot invalidation', 'syncAttributes()-এ pivot sync-এর পরে DB::afterCommit দিয়ে shop-filters clear।'],
  ['adminpanel/app/Http/Controllers/SettingController.php', 'Settings write', 'Controller-এর পুরোনো manual cache clear সরানো হয়েছে; Eloquent event observer চালায়।'],
  ['adminpanel/app/Http/Controllers/HomeSliderController.php', 'Slider write', 'Eloquent event → observer → sliders group।'],
  ['adminpanel/app/Http/Controllers/AboutPageController.php', 'About write', 'Eloquent update → observer → about group।'],
  ['adminpanel/app/Http/Controllers/ShippingMethodController.php', 'Shipping write', 'Admin controller; API ShippingMethodController থেকে আলাদা। event দিয়ে invalidation।'],
  ['adminpanel/app/Models/Section.php', 'Menu loader', 'sections() relations load করে array দেয়; ContentCache এই array রাখে।'],
  ['adminpanel/config/cache.php', 'Cache store configuration', 'CACHE_STORE; database/Redis store; cache lock connection।'],
  ['adminpanel/config/database.php', 'Database ও Redis connection configuration', 'Redis cache connection-এর timeout/read timeout/retry এখানে। এতে env variable-এর নাম আছে, secret value নেই।'],
  ['adminpanel/database/migrations/0001_01_01_000001_create_cache_table.php', 'আগের Laravel cache tables', 'Database cache হলে cache এবং cache_locks দরকার; ইতিমধ্যে থাকলে আবার তৈরি নয়।'],
  ['adminpanel/routes/api.php', 'Frontend → controller route mapping', '/menu, /about, /contact, /listing, /shipping-methods এবং /locations routes।'],
  ['frontend/app/composables/useCatalogMenu.ts', 'Nuxt shared fetch', 'useFetch, catalog-menu key, API baseURL, lazy load।'],
  ['frontend/app/app.vue', 'Frontend refresh trigger', 'Mounted এবং route.path বদলালে refresh; একই সময়ের request dedupe।'],
  ['frontend/app/components/AppFooter.vue', 'Footer consumer', 'useCatalogMenu-এর site fields থেকে contact/footer output।'],
  ['frontend/app/components/SiteLogo.vue', 'Logo consumer', 'একই shared data থেকে logo/name।'],
  ['frontend/app/components/AppHeader.vue', 'Header consumer', 'একই shared catalog data ব্যবহার করে।'],
  ['frontend/app/components/HomeHeader.vue', 'Home header consumer', 'Shared menu data ব্যবহার করে।'],
  ['frontend/nuxt.config.ts', 'API environment, prerender ও stylesheet hash', 'Laravel content cache-এর বাইরে static HTML ও CSS cache-busting।'],
  ['adminpanel/app/Http/Controllers/Api/VisitorController.php', 'আলাদা GeoIP cache', '৭ দিনের Cache::get/put; ContentCache fallback-এর অংশ নয়।'],
  ['adminpanel/tests/Feature/ContentCacheTest.php', 'Cache correctness/outage tests', 'SQLite memory database, fake failure store এবং real database cache driver recovery।'],
  ['adminpanel/tests/Feature/FilterCachePerformanceTest.php', 'বড় fixture ও query-count test', '২,০০০ products/৪,০০০ variants; SQL distinct aggregation ও warm-cache check।'],
  ['adminpanel/tests/Feature/AdminPerformanceTest.php', 'আগের performance checks', 'Category inheritance query count ও public clear-cache route নিষিদ্ধ থাকার test।'],
  ['adminpanel/phpunit.xml', 'নিরাপদ test environment', 'SQLite :memory:, array cache, testing environment; production DB ব্যবহার নয়।'],
]

section('start', '১. প্রথমে কী বুঝবেন', `
<p>এই guide আপনার Laravel + Nuxt project-এর বর্তমান cache implementation বোঝার ও অন্য project-এ বসানোর জন্য। <strong>আগে ১–৬ পড়ুন, তারপর ৭ নম্বরের integration ধাপ অনুসরণ করুন।</strong> শেষে প্রতিটি সংশ্লিষ্ট file-এর সম্পূর্ণ source snapshot আছে।</p>
<div class="cards"><div><b>Read</b><span>Controller → ContentCache → cache অথবা database</span></div><div><b>Edit</b><span>Model event → observer → version বদল + group clear</span></div><div><b>Recovery</b><span>Cache ফিরলে database version দিয়ে পুরোনো data শনাক্ত</span></div></div>
<p>এই implementation-এ content-এর application TTL ৬ ঘণ্টা বা ৩০ দিন নয়। <code>Cache::forever()</code> ব্যবহার হয়। Store eviction, manual removal, restart অথবা backend-এর নিজস্ব retention-এর কারণে entry হারাতে পারে; entry না থাকলে আবার database থেকে তৈরি হয়।</p>
<div class="note">“Cache কাজ করছে” মানে database পুরো বন্ধ নয়। প্রতিটি ContentCache read-এ version জানার জন্য একটি ছোট primary-key query হয়। বড় content query cache hit হলে এড়ানো যায়।</div>
${rows([['শব্দ','সহজ অর্থ'],['group','একসঙ্গে invalidation হবে এমন data: settings, menu, shop-filters।'],['name','group-এর ভেতরের data-এর নাম: active, data, price.all।'],['loader / closure','Cache না মিললে যে function database query করে data ফেরত দেয়।'],['version','Database-এ থাকা UUID; edit-এর পরে বদলায়।'],['lock','একই group-এর cache তৈরি/মোছা সাময়িকভাবে সমন্বয় করে।'],['observer','Model-এর save/delete-এর event শুনে cache clear করে।'],['after commit','Transaction সফল হয়ে database-এ স্থায়ী হওয়ার পরে কাজ করা।']])}`)

section('flow', '২. কোন file থেকে কোন file-এ data যায়', `
<h3>Customer website খোলার সময়</h3>
<div class="flow"><span>Nuxt AppFooter / SiteLogo / Header</span><i>↓</i><span>useCatalogMenu.ts → GET /api/menu</span><i>↓</i><span>routes/api.php → FrontController::menu()</span><i>↓</i><span>ContentCache::remember() / PageSeo → SiteSettings</span><i>↓</i><span>content_cache_versions query → valid cache? → data</span><i>↓ না থাকলে</i><span>Model query → array → cache save → JSON → Nuxt UI</span></div>
<h3>Admin address edit করলে</h3>
<ol><li><code>SettingController</code> validation করে <code>Setting</code> save করে।</li><li><code>AppServiceProvider</code>-এ attach করা <code>ContentCacheObserver::saved()</code> চলে।</li><li>Transaction থাকলে commit-এর পরে <code>forget('settings')</code> চলে।</li><li><code>content_cache_versions</code>-এ settings-এর নতুন UUID রাখা হয়।</li><li>Cache পাওয়া গেলে settings group-এর actual পুরোনো key-গুলো delete হয়।</li><li>পরের menu/contact/SEO read-এ নতুন version মিলিয়ে fresh settings load হয়।</li><li>Nuxt mounted/path navigation refresh হলে customer-এর shared UI নতুন data পায়।</li></ol>
<p>খোলা page-এ admin edit-এর সঙ্গে সঙ্গে push notification বা live update হয় না। এখন কোনো websocket/polling subscription নেই।</p>
<h3>Cache outage-এর সময়</h3><div class="flow horizontal"><span>Cache read ব্যর্থ</span><i>→</i><span>Database loader</span><i>→</i><span>Fresh response</span></div><p>Outage-এর মাঝে edit হলে database version বদলে থাকে। Redis/cache ফিরে এসে পুরোনো entry দিলে তার version মিলবে না; fresh load হবে। এক request ইতিমধ্যে চলার সময় edit হলে ওই request আগের snapshot পেতে পারে; পরবর্তী read নতুন version পরীক্ষা করে।</p>`)

section('files', '৩. File map — কোথায় কী আছে', `
<p>সব path repository root থেকে। অন্য project-এ <code>adminpanel/</code> আপনার Laravel root এবং <code>frontend/</code> আপনার Nuxt root হিসেবে ধরবেন। নিচের link চাপলে এই document-এর embedded source খুলবে।</p>
${rows([['File','দায়িত্ব'], ...files.map(([file,title,desc],i)=>[`<a href="#source-${i}"><code>${escape(file)}</code></a>`, `<b>${title}</b><br>${desc}`])])}
<p><code>ProductController</code>-এর transactional product/variant save এবং অন্য registered model-এর normal Eloquent writes-ও observer দিয়ে এই system-এ যুক্ত হয়। প্রতিটি controller-এ আলাদা cache helper বসাতে হয় না। একটি model-এ নতুন write path যোগ করলে event bypass হচ্ছে কি না পরীক্ষা করবেন।</p>`)

section('groups', '৪. কোন edit-এ কোন cache clear হয়', `
${rows([['Model / change','Group','Cached data'],['Setting','settings','active settings array/null'],['HomeSlider','sliders','active ordered sliders'],['AboutPage','about','about content'],['ShippingMethod','shipping','active shipping methods'],['Division / District / Upazila','locations','division, district, upazila lists'],['Product / Category / Section','menu + shop-filters','category tree/counts ও filter data'],['Brand / ProductAttributeDefinition / ProductAttributeValue','shop-filters','brand, attribute, price scopes'],['ProductVariant create/delete/restore','shop-filters','filter scopes'],['Existing ProductVariant price/status/product_id change','shop-filters','price বা active product association বদল'],['Existing variant শুধু stock/cost/SKU/threshold change','clear নয়','এই filter query-তে ওই fields dependency নয়'],['Category attribute pivot sync','shop-filters','CategoryController-এ explicit afterCommit invalidation']])}
<p>Observer-এর <code>saved</code>, <code>deleted</code>, <code>restored</code> method আছে। Restore event কেবল model-এ soft-delete/restore সমর্থন থাকলে প্রযোজ্য।</p>
<h3>Key-এর উদাহরণ</h3>${code(`content.v1.settings.active
content.v1.settings.keys
content.v1.settings.lock
content.v1.menu.data
content.v1.sliders.data
content.v1.shop-filters.price.all
content.v1.shop-filters.attributes.{scope-hash}`, 'Logical keys; configured store prefix আলাদাভাবে যুক্ত হয়')}
<p><code>.keys</code> registry-তে group-এর entry name থাকে। পুরো group invalidate করলে সব scope সরানো হয়। <code>scope</code> filter-এর category/attribute IDs sort করে hash করা হয়। এতে এক category-এর count অন্য category-তে যায় না।</p>`)

section('engine', '৫. ContentCache-এর code সহজভাবে', `
<ol><li><code>DB::transactionLevel()</code>: transaction-এর ভেতরে সরাসরি loader; uncommitted data shared cache-এ যাবে না।</li><li><code>content_cache_versions</code>: current group version পড়া হয়। Row না থাকলে <code>initial</code>; table নিজে অবশ্যই থাকতে হবে।</li><li><code>matches()</code>: envelope-এর version সমান এবং value key থাকলেই hit। null-ও বৈধ cached value।</li><li>Miss হলে group lock নেওয়ার চেষ্টা: lease ৬০ সেকেন্ড, wait সর্বোচ্চ ২ সেকেন্ড। এটি content expiry নয়।</li><li>Lock পাওয়া গেলে cache আরেকবার পড়া হয়; অন্য request ইতিমধ্যে rebuild করেছে কি না দেখা হয়।</li><li>Loader থেকে data আসে; registry ও <code>['version' =&gt; ..., 'value' =&gt; ...]</code> forever রাখা হয়।</li><li><code>finally</code>-তে lock release হয়। Cache write ব্যর্থ হলেও পাওয়া data ফেরত যায়।</li></ol>
<h3>forget() কী করে</h3><p>প্রথমে primary database-এ নতুন UUID <code>upsert</code>, তারপর lock নিয়ে registered cache key delete। Cleanup ব্যর্থ হলেও version পরিবর্তনের কারণে old envelope আর valid নয়। Failed cleanup-এর obsolete entry physical store-এ থেকে যেতে পারে; সেই key পরে load হলে overwrite হবে।</p>
<h3>Fallback-এর সীমা</h3><p><code>cacheOperation()</code> শুধু cache operation-এর exception ধরছে। Loader-এর SQL error, missing version table, main database outage ইচ্ছাকৃতভাবে success হিসেবে ফেরত দেওয়া হয় না। Warning log-এ exception class যায়; password/raw cache data যায় না। Log rotation/alert service আপনাকে server-এ configure করতে হবে।</p>
<p>৬০ সেকেন্ডের lock lease-এর মধ্যে কাজ শেষ হওয়া ধরে নেওয়া হয়েছে; এটি distributed transaction নয়। দীর্ঘ query ও অত্যধিক concurrent request production load test-এ যাচাই করুন। Outage-এ সব request database-এ পড়তে পারে, তাই fallback থাকলেই unlimited load capacity হয় না।</p>`)

section('setup', '৬. এই project-এ install ও deploy', `
<p>বর্তমান backend-এর composer requirement: PHP <code>^8.3</code>, Laravel <code>^13.8</code>; frontend Nuxt <code>^4.5.2</code>। Database cache-এর জন্য নতুন package লাগে না। Redis service নিজে install/provision করা এই কাজের অংশ হিসেবে করা হয়নি।</p>
<ol><li>Backend code ও migration deploy করার সময় maintenance/release window নিন; নতুন code যেন migration-এর আগে request serve না করে।</li><li>Laravel root <code>adminpanel</code>-এ নিচের migration চালান। Migration আগে হয়ে থাকলে আবার table বানাবে না।</li><li>Config cache rebuild করুন; queue/Octane থাকলে deployment পদ্ধতি অনুযায়ী workers reload করুন।</li><li>Nuxt code deploy করুন। Static deployment হলে build-time API URL ঠিক করে regenerate করুন।</li><li>Settings edit → menu/contact read → route navigation-এর smoke check করুন।</li></ol>
${code(`cd adminpanel
php artisan migrate:status --path=database/migrations/2026_10_04_000001_create_content_cache_versions_table.php
php artisan migrate --path=database/migrations/2026_10_04_000001_create_content_cache_versions_table.php --force
php artisan config:cache`, 'Deploy commands — প্রয়োজনমতো maintenance window-এ')}
${code(`CACHE_STORE=database`, '.env — বর্তমান database store')}
<p><code>cache</code> এবং <code>cache_locks</code> table Laravel-এর আগের migration-এ আছে। একেবারে নতুন project-এ না থাকলে নিজের framework-এর cache migration তৈরি করে migrate করুন; একই নামের existing migration/table আবার তৈরি করবেন না।</p>
<p><code>content_cache_versions</code> মাত্র group/version রাখে। Redis ব্যবহার করলেও table লাগে। এটি truncate/drop করলে পুরোনো cache-এর version authority নষ্ট হবে। App data restore করলে cache data-ও সমন্বয়/clear করতে হবে।</p>
<h3>Redis পরে চালু করলে</h3>${code(`CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CACHE_CONNECTION=cache
REDIS_CACHE_LOCK_CONNECTION=cache
REDIS_CACHE_TIMEOUT=1
REDIS_CACHE_READ_TIMEOUT=1
REDIS_CACHE_MAX_RETRIES=0`, 'উদাহরণ — actual server endpoint/credentials আপনার hosting অনুযায়ী')}
<p>এই repository-তে <code>predis/predis</code> dependency আছে। নতুন project-এ Predis বেছে নিলে <code>composer require predis/predis</code> লাগতে পারে। বিকল্প <code>REDIS_CLIENT=phpredis</code> হলে server-এর PHP Redis extension লাগবে। Client package থাকলেই Redis server তৈরি হয় না; Hostinger plan-এ availability আগে যাচাই করুন। এখানে Redis client switch করা হয়নি।</p>
<p>Config-এ cache connection এবং lock connection একই <code>cache</code> connection; timeout/retry সেই connection-এর। আলাদা lock connection দিলে তার timeout-ও ঠিক করুন। <code>failover</code> store config-এ আছে, কিন্তু এই guide-এর default নয়। প্রতিটি application-এর cache prefix আলাদা দিন; multi-instance হলে একই app-এর shared database/cache ব্যবহার করুন।</p>`)

section('port', '৭. অন্য project-এ বসাবেন: আগে কোনটা, পরে কোনটা', `
<ol><li>নিজের project-এর supported PHP/Laravel version, model নাম, database table এবং existing cache flow দেখুন। এই project-এর সব ecommerce controller অন্ধভাবে copy করবেন না।</li><li>Source appendix থেকে <code>ContentCache.php</code> এবং version migration একই relative Laravel path-এ রাখুন।</li><li>Migration চালান, তারপর database cache store configure করুন।</li><li>আপনার model-এর জন্য reader তৈরি করুন। Settings model থাকলে <code>SiteSettings</code>-এর query-তে আপনার field/status/order মিলিয়ে নিন।</li><li>Observer-এর model-to-group mapping আপনার project অনুযায়ী লিখুন। এখানে নেই এমন model register করবেন না।</li><li>AppServiceProvider-এর existing <code>boot()</code>-এর মধ্যে observer registration যোগ করুন। পুরো provider overwrite করবেন না।</li><li>Controller/API-তে reader বা <code>ContentCache::remember()</code> ব্যবহার করুন। Model/Collection নয়, array/scalar/null cache রাখা সহজ।</li><li>Save/delete স্বাভাবিক Eloquent event দিয়ে করুন; bulk/pivot write-এ নিচের afterCommit নিয়ম দিন।</li><li>Nuxt হলে API route/baseURL, composable ও app.vue refresh যুক্ত করুন। অন্য frontend হলে তার fetch lifecycle অনুযায়ী করুন।</li><li>Tests নতুন schema/model অনুযায়ী adapt করে isolated test DB-তে চালান।</li></ol>
${code(String.raw`// AppServiceProvider.php — existing boot() method-এর ভিতরে:
\App\Models\Setting::observe(\App\Observers\ContentCacheObserver::class);`, 'শুধু Setting model থাকা ছোট project-এর registration example')}
${code(String.raw`use App\Models\Setting;
use App\Support\ContentCache;

$settings = ContentCache::remember('settings', 'active', function () {
    $record = Setting::query()->where('status', true)->latest('id')->first();
    if (!$record) {
        return null;
    }
    return $record->toArray();
});`, 'Controller বা reader-এর read example — সহজ if/function syntax')}
<p><code>remember</code>-এর group আর observer-এর group অবশ্যই এক হতে হবে। <code>settings</code> দিয়ে read করে <code>setting</code> clear করলে কাজ হবে না। Closure হলো “cache না পেলে তখন এই query চালাও” function। <code>fn () =&gt;</code> একই ছোট function-এর syntax; source snapshot-এ যে syntax আছে তা হুবহু রাখা হয়েছে।</p>
${code(String.raw`use App\Models\Setting;

$setting = Setting::findOrFail($id);
$setting->update(['address' => $validatedAddress]);
// Observer registered থাকলে settings group নিজে invalidate হবে.`, 'Normal model update — নিজের validated input ব্যবহার করবেন')}
<h3>Bulk update / import / pivot sync</h3>${code(String.raw`use App\Support\ContentCache;
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($category, $attributes) {
    $category->attributes()->sync($attributes);
    DB::afterCommit(function () {
        ContentCache::forget('shop-filters');
    });
});`, 'Pivot example — CategoryController-এর কাজ বোঝার জন্য')}
<p><code>DB::table(...)-&gt;update()</code>, bulk Eloquent update/delete, <code>saveQuietly()</code>, <code>withoutEvents()</code>, pivot attach/detach/sync সাধারণ model saved event চালায় না। Query শেষে <em>সব dependent group</em> invalidate করুন। Product bulk edit হলে menu ও shop-filters দুটোই লাগতে পারে।</p>
<p>Observer commit-এর পরে invalidation চালায়; domain write ও version write একই atomic transaction নয়। Commit হয়ে process crash করলে invalidation বাদ পড়ার সম্ভাবনা পুরোপুরি দূর হয়নি। শক্ত consistency দরকার হলে durable outbox/transactional version updates আলাদা design করতে হবে। এখানে তা implemented দাবি করা হচ্ছে না।</p>`)

section('frontend', '৮. Nuxt frontend-এ connection', `
<p><code>useCatalogMenu.ts</code>-এর <code>key: 'catalog-menu'</code> দিয়ে একই Nuxt app-এর consumers shared async data পায়। এটি Redis key নয়। <code>lazy: true</code> navigation-কে menu fetch শেষ হওয়া পর্যন্ত আটকে রাখে না।</p>
${code(`const { data: catalogData, refresh: refreshCatalog } = useCatalogMenu()
const route = useRoute()

onMounted(function () {
  void refreshCatalog({ dedupe: 'defer' })
})

watch(function () {
  return route.path
}, function () {
  void refreshCatalog({ dedupe: 'defer' })
})`, 'বর্তমান app.vue-এর equivalent সহজ function syntax — duplicate করে যোগ করবেন না')}
<p><code>dedupe: 'defer'</code> মানে ওই key-এর request চললে নতুন duplicate request না শুরু করা। Search/sort/pagination-এর query string বদলালে <code>route.path</code> বদলায় না, তাই shared menu refetch হয় না।</p>
${code(`// Nuxt environment — নিজের backend URL দিন
NUXT_PUBLIC_API_BASE=https://your-backend.example/api
NUXT_PUBLIC_BACKEND_BASE=https://your-backend.example`, 'এগুলো public URL; secret key এখানে নয়')}
<p>Backend-এর CORS ও frontend URL নিজের domain অনুযায়ী configure করতে হবে। Static build-এ build-time value গুরুত্বপূর্ণ; শুধু server-এর env বদলালে already-generated bundle বদলায় না।</p>
<div class="note">Laravel cache invalidate করলে Nuxt-generated HTML, social-preview metadata বা CDN নিজে rewrite হয় না। Static SEO update চাইলে frontend regenerate/redeploy এবং প্রযোজ্য CDN purge আলাদাভাবে করতে হবে।</div>
<p><code>nuxt.config.ts</code>-এ stylesheet content hash দিয়ে CSS URL version আছে। এটি asset cache-busting। Browser/cache/CDN data freshness আর Laravel content cache আলাদা স্তর।</p>`)

section('coverage', '৯. কোথায় cache আছে, কোথায় নেই', `
${rows([['Data','বর্তমান আচরণ'],['Settings/menu/sliders/about/shipping/locations/filter metadata','ContentCache forever + change invalidation + cache-failure fallback'],['Hot deals / trending products','/menu response-এ থাকলেও এই query-গুলো cached sections/sliders-এর বাইরে live'],['Product listing/detail/recommended/search','সরাসরি query; listing-এ pagination/limit আছে'],['Checkout/stock/payment/order/customer account','এই public content cache দিয়ে পুরোনো response পরিবেশন করা হয় না'],['Visitor country/city lookup','VisitorController-এ ৭ দিনের Cache::get/put; একই fallback helper নয়'],['Rate limits/session/verification/security','নিজস্ব cache/storage ও expiry; ContentCache fallback security bypass করে না'],['Blade/config/route cache','Laravel compiled code/config cache; content data cache নয়'],['Nuxt payload/static HTML/CDN','Deployment ও client refresh-এর আলাদা স্তর']])}
<p>GeoIP provider-এর mapping admin edit ছাড়া বদলাতে পারে, তাই তার ৭ দিনের TTL রাখা। VisitorController-এর direct cache get/put ব্যর্থ হলে ContentCache সেটি ধরবে না। Documentation-এ পুরো website “cache outage-proof” দাবি করা হচ্ছে না।</p>
<h3>আগের কোন code সরেছে</h3><p>পুরোনো <code>BrandObserver</code>, <code>CategoryObserver</code>, <code>ProductAttributeObserver</code>, <code>ProductObserver</code>, <code>SectionObserver</code> ও <code>ShopFilterCache</code> unified helper/observer দিয়ে প্রতিস্থাপিত। AboutPage, HomeSlider, Setting ও admin ShippingMethod controller-এর পুরোনো duplicate manual cache clear সরেছে। অন্য project-এ পুরোনো observer reference আগে খুঁজে migrate করবেন; নাম দেখে unrelated class delete নয়।</p>`)

section('checks', '১০. Practical verification ও কীভাবে আবার test করবেন', `
<div class="note">যাচাইয়ের তারিখ: ৫ অক্টোবর ২০২৬ (বাংলাদেশ সময়)। নিচের ফল এই workspace-এর স্থানীয় checks; production Redis shutdown/load benchmark নয়।</div>
<div id="verification">__VERIFICATION__</div>
${code(`cd adminpanel
php artisan test --compact --filter='ContentCacheTest|AdminPerformanceTest|FilterCachePerformanceTest'`, 'Isolated tests — phpunit.xml-এ SQLite :memory: নিশ্চিত করুন')}
${rows([['Test scenario','কী যাচাই হয়'],['৩০ দিন সময় এগোনো','শুধু সময় পার হলে content loader আবার চলে না'],['Create/edit/status/delete','Settings consumers fresh data পায়'],['Rollback বনাম commit','Rollback cache রাখে; commit invalidate করে'],['Stock-only বনাম price/status edit','Unnecessary filter invalidation এড়ানো'],['Cache lock busy','Fresh uncached read ফেরত আসে'],['Cache write failure','Loader একবার চলে; fresh result ফেরত আসে'],['Outage + edit + recovery','Store-এর old data রেখে recovery করলে old version গ্রহণ হয় না'],['Database cache table unavailable','Test-এর memory DB-তে cache table rename করে real store exception/fallback check'],['Loader বা version DB failure','গুরুতর database error cached success দিয়ে ঢেকে দেওয়া হয় না'],['২,০০০ products / ৪,০০০ variants','SQL distinct count সঠিক; warm read শুধু version query করে'],['Legacy envelope','Version ছাড়া old cache entry পুনর্নির্মাণ হয়']])}
<h3>Staging-এ হাতে পরীক্ষা</h3><ol><li>একটি test setting save করুন; menu/contact API-তে একই value দেখুন।</li><li>আবার read করুন; content query পুনরায় না চলা test দিয়ে মিলান।</li><li>Setting edit করে API ও frontend navigation-এ নতুন value দেখুন।</li><li>শুধু dedicated staging cache service বন্ধ/restore করে edit/recovery check করুন; production বা shared Redis বন্ধ করবেন না।</li><li>Database পুরো বন্ধ করলে checkout চালু থাকবে এমন আশা করবেন না। Monitoring ও user-friendly service error page আলাদা deployment concern।</li></ol>
<p>Migration/status এবং automated checks নিচে যেভাবে report করা হয়েছে তার বেশি নিশ্চয়তা এই document দিচ্ছে না। Database/hosting outage, arbitrary programming bug বা অসীম traffic-এ “কোনোভাবেই error হবে না”—এমন প্রতিশ্রুতি বাস্তবসম্মত নয়।</p>`)

section('troubleshoot', '১১. Error হলে কোথায় দেখবেন', `
${rows([['লক্ষণ','আগে কী পরীক্ষা করবেন'],['content_cache_versions table missing','সঠিক DB connection-এ migration চালানো হয়েছে কি না; config cache পুরোনো কি না।'],['Edit-এর পরও API old data','Observer registered? saved event? bulk/pivot bypass? read/forget group একই?'],['API fresh কিন্তু footer old','Nuxt API URL, shared refresh, deployed frontend bundle ও CDN পরীক্ষা।'],['Cache unavailable log','Configured store/client/timeout/Redis endpoint বা cache table access পরীক্ষা।'],['Redis থেকে database store-এ rollback','Version table অক্ষত রাখুন; shared app configuration consistent করুন; workers reload।'],['null settings','Active Setting আছে কি না; status/latest query project-এর field অনুযায়ী কি না।'],['IDE red line','PHP version, Composer dependencies ও import/type যাচাই; diagnostics বন্ধ নয়।'],['Warm read-এও DB query','Version lookup ইচ্ছাকৃত; menu-এর live hot/trending queries-ও আছে।'],['Outage-এ server slow','Fallback traffic DB-তে পড়ে; connection timeouts, DB capacity, indexes ও monitoring পরীক্ষা।']])}
<p>Admin CRUD save-এর সময় global <code>Cache::flush()</code> বা <code>php artisan cache:clear</code> দিয়ে সব app data উড়িয়ে দেবেন না। Targeted <code>ContentCache::forget('settings')</code> ব্যবহার করুন। User-facing public cache-clear route যুক্ত করবেন না।</p>
<p>এই guide offline HTML: CDN/font/library লাগে না। Search দিয়ে section খুঁজুন, source খুলে copy করুন, Print/PDF ব্যবহার করুন। Source snapshot code পরিবর্তনের সঙ্গে নিজে update হয় না; repository root থেকে <code>node tools/build-cache-guide.mjs</code> চালালে নতুন snapshot হয়। Generator app/database বদলায় না; test পুনরায় না চালিয়ে পুরোনো verification-কে নতুন ফল বলা যাবে না।</p>`)

const snapshots = files.map(([file,title,desc],i)=>{
  const source = fs.readFileSync(path.join(root,file),'utf8')
  const hash = createHash('sha256').update(source).digest('hex')
  return `<details id="source-${i}" class="source"><summary><b>${escape(title)}</b><code>${escape(file)}</code></summary><p>${desc}</p><p class="muted">Source snapshot SHA-256: <code>${hash}</code></p>${code(source,file)}</details>`
}).join('\n')
section('sources','১২. Project-এর আসল code — সম্পূর্ণ file snapshots', `<p>Core helper, migration, observer, provider, controller, config, Nuxt এবং test—সব সংশ্লিষ্ট file নিচে রাখা। বড় controller-এ cache-বহির্ভূত code-ও আছে; source বোঝার জন্য সম্পূর্ণ দেওয়া, অন্য project-এ পুরো controller copy করার নির্দেশ নয়। কোনো <code>.env</code> বা secret file অন্তর্ভুক্ত করা হয়নি।</p>${snapshots}`)

const verificationPath = path.join(root,'tools/cache-guide-verification.json')
const verification = fs.existsSync(verificationPath) ? JSON.parse(fs.readFileSync(verificationPath,'utf8')) : null
const evidence = verification ? `<p><strong>${escape(verification.date)}</strong></p><ul>${verification.checks.map(item=>`<li>${escape(item)}</li>`).join('')}</ul><p>${escape(verification.limit)}</p>` : '<p>এই build-এর সঙ্গে verification record নেই। উপরের test command চালিয়ে ফল যাচাই করুন।</p>'
const html = `<!doctype html>
<html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Laravel + Nuxt Cache — সম্পূর্ণ বাংলা Guide</title>
<style>
:root{--ink:#172a3a;--muted:#566a7b;--line:#dae4ec;--brand:#087f8c;--paper:#fff}*{box-sizing:border-box}html{scroll-behavior:smooth;scroll-padding-top:24px}body{margin:0;background:#eff4f7;color:var(--ink);font:16px/1.85 system-ui,"Nirmala UI",sans-serif}a{color:#076778;text-underline-offset:3px}header{background:#102b3a;color:white;padding:54px max(24px,calc((100vw - 1280px)/2))}header p{max-width:850px;color:#d4e4ed}h1{font-size:clamp(28px,4vw,44px);line-height:1.35;margin:12px 0}h2{font-size:26px;line-height:1.5}h3{font-size:20px;margin-top:30px}.eyebrow{font-size:13px;letter-spacing:2px;color:#74e1d4}.layout{max-width:1340px;margin:auto;display:grid;grid-template-columns:250px minmax(0,1fr);gap:28px;padding:32px 24px}nav{position:sticky;top:20px;align-self:start;max-height:calc(100vh - 40px);overflow:auto}nav a{display:block;text-decoration:none;padding:8px 12px;border-radius:7px;font-size:14px}nav a:hover{background:#dce9ed}input{width:100%;padding:12px;border:1px solid #b4c8d4;border-radius:8px;font:inherit;margin-bottom:12px}section{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:28px;margin-bottom:22px;min-width:0}section h2{margin-top:0}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.cards>div{background:#edf7f6;padding:16px;border-radius:9px}.cards b,.cards span{display:block}.note{border-left:4px solid #d99524;background:#fff8e8;padding:16px 20px;margin:22px 0}.flow{display:flex;flex-direction:column;align-items:center;gap:5px;background:#eef6fa;padding:20px;border-radius:10px}.flow span{background:white;border:1px solid #a8cbd5;border-radius:8px;padding:12px;text-align:center;max-width:100%}.flow i{font-style:normal;color:#087f8c}.horizontal{flex-direction:row;flex-wrap:wrap;justify-content:center}.table-wrap{overflow:auto}table{border-collapse:collapse;width:100%;font-size:14px}td{vertical-align:top;border:1px solid var(--line);padding:11px}tr:first-child{background:#e8f1f5;font-weight:700}code{font-family:Consolas,monospace;overflow-wrap:anywhere;font-size:.88em}p code,li code,td code{background:#edf2f6;padding:2px 4px;border-radius:4px}.codebox{background:#122332;color:#e3eef7;border-radius:9px;margin:18px 0;overflow:hidden}.codebar{display:flex;align-items:center;justify-content:space-between;gap:15px;background:#1e384b;padding:10px 14px;font:13px/1.5 Consolas,monospace;overflow-wrap:anywhere}pre{margin:0;padding:18px;overflow:auto;font:14px/1.65 Consolas,monospace;tab-size:4}pre code{font-size:inherit;overflow-wrap:normal}button{cursor:pointer;border:1px solid #a7c9d0;border-radius:6px;padding:8px 12px;background:#eaf7f5;color:#123c48;font:inherit}header button{margin-right:8px}.source{border:1px solid var(--line);border-radius:9px;padding:16px;margin:14px 0}.source summary{cursor:pointer}.source summary code{display:block;color:var(--muted);font-size:12px}.source[open] summary{margin-bottom:15px}.muted{color:var(--muted);font-size:12px}.status{min-height:24px;font-size:13px}footer{padding:24px;text-align:center;color:var(--muted)}[hidden]{display:none!important}@media(max-width:900px){.layout{grid-template-columns:1fr}nav{position:static;max-height:none}.cards{grid-template-columns:1fr}section{padding:20px}header{padding:30px 24px}}@media print{body{background:white;font-size:11pt}.layout{display:block;padding:0}nav,.actions,.copy,.status{display:none}header{padding:20px;color:#000;background:white}header p,.eyebrow{color:#333}section{border:0;padding:10px;break-before:auto}.codebox{color:#000;background:#f3f3f3}.codebar{background:#e8e8e8;color:#000}pre{white-space:pre-wrap;overflow-wrap:anywhere;font-size:9pt}table{font-size:10pt}.flow,.cards{break-inside:avoid}h2,h3{break-after:avoid}a{color:#000}}
main{min-width:0}.codebar span{min-width:0}.codebar button{flex-shrink:0}
</style></head><body>
<header><div class="eyebrow">PROJECT HANDBOOK · LARAVEL + NUXT</div><h1>Cache বুঝুন, যুক্ত করুন,<br>পরীক্ষা করে deploy করুন।</h1><p>বাংলায় A–Z implementation guide • File map • Data flow • Copyable source • Outage ও recovery • অন্য project-এ integration</p><div class="actions"><button type="button" id="print">Print / Save PDF</button><button type="button" id="expand">সব source খুলুন</button></div></header>
<div class="layout"><nav aria-label="সূচিপত্র"><label for="search">Guide-এ খুঁজুন</label><input id="search" type="search" placeholder="যেমন: Redis, observer, migration"><div id="search-status" class="status" role="status"></div>${sections.map(s=>`<a href="#${s.id}">${s.title}</a>`).join('')}</nav><main>${sections.map(s=>`<section id="${s.id}"><h2>${s.title}</h2>${s.body}</section>`).join('\n').replace('__VERIFICATION__',evidence)}</main></div>
<footer>একটি offline document · Sources are snapshots, not automatic live updates · কোনো secret অন্তর্ভুক্ত নয়</footer>
<script>
const sources = document.querySelectorAll('details.source');
document.getElementById('expand').addEventListener('click', function () {
  const open = this.textContent.includes('খুলুন');
  sources.forEach(function (item) { item.open = open; });
  this.textContent = open ? 'সব source বন্ধ করুন' : 'সব source খুলুন';
});
document.getElementById('print').addEventListener('click', function () { window.print(); });
let printStates = [];
window.addEventListener('beforeprint', function () { printStates = Array.from(sources).map(function (item) { const wasOpen = item.open; item.open = true; return wasOpen; }); });
window.addEventListener('afterprint', function () { sources.forEach(function (item, index) { item.open = printStates[index]; }); });
document.querySelectorAll('.copy').forEach(function (button) {
  button.addEventListener('click', async function () {
    const text = button.closest('.codebox').querySelector('code').textContent;
    try {
      if (!navigator.clipboard) throw new Error('Clipboard unavailable');
      await navigator.clipboard.writeText(text);
      button.textContent = 'Copied';
    } catch (error) {
      const selection = window.getSelection();
      const range = document.createRange();
      range.selectNodeContents(button.closest('.codebox').querySelector('code'));
      selection.removeAllRanges(); selection.addRange(range);
      button.textContent = 'Selected — Ctrl+C';
    }
    setTimeout(function () { button.textContent = 'Copy code'; }, 2500);
  });
});
const search = document.getElementById('search');
search.addEventListener('input', function () {
  const value = search.value.trim().toLowerCase(); let count = 0;
  document.querySelectorAll('main > section').forEach(function (item) { item.hidden = value !== '' && !item.textContent.toLowerCase().includes(value); if (!item.hidden) count++; });
  document.getElementById('search-status').textContent = value ? count + 'টি section পাওয়া গেছে' : '';
});
function revealTarget() {
  const item = document.getElementById(location.hash.slice(1));
  if (!item) return;
  const section = item.closest('section'); if (section) section.hidden = false;
  if (item.tagName === 'DETAILS') item.open = true;
}
window.addEventListener('hashchange', revealTarget); revealTarget();
</script></body></html>`
const output = path.join(root,'adminpanel/docs/CACHE_GUIDE_BN.html')
fs.writeFileSync(output,html,'utf8')
console.log(`Generated ${output}; ${files.length} source snapshots; ${Buffer.byteLength(html)} bytes.`)
