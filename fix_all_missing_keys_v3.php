<?php
// fix_all_missing_keys_v3.php — ALL missing keys, correct scope
require 'vendor/autoload.php';

function phpLint($code) {
    $t = tempnam(sys_get_temp_dir(), 'p_') . '.php';
    file_put_contents($t, $code);
    exec('php -l ' . escapeshellarg($t) . ' 2>&1', $o, $rc);
    unlink($t);
    return [$rc === 0, implode("\n", $o)];
}

echo "═══ COMPLETE I18N KEYS — CORRECT SCOPE ═══\n\n";

// ═══════════════════════════════════════════════════════════════
// PROVIDER-ONLY KEYS (EN/NP मात्र)
// ═══════════════════════════════════════════════════════════════
$providerKeys = [
    // 19 unresolved
    'artist'                   => ['en'=>'Artist','np'=>'कलाकार'],
    'no_artworks_filter'       => ['en'=>'No artworks match this filter','np'=>'यो फिल्टरमा कुनै कलाकृति भेटिएन'],
    'no_custom_orders_yet'     => ['en'=>'No custom orders yet','np'=>'अझै कुनै कस्टम अर्डर छैन'],
    'unassigned'               => ['en'=>'Unassigned','np'=>'नतोकिएको'],
    'inventory_dashboard_title'=> ['en'=>'Inventory Dashboard','np'=>'इन्भेन्टरी ड्यासबोर्ड'],
    'kyc_inventory_message'    => ['en'=>'Complete KYC to unlock inventory features.','np'=>'इन्भेन्टरी सुविधाहरू खोल्न KYC पूरा गर्नुहोस्।'],
    'stock_already_zero'       => ['en'=>'Stock is already zero.','np'=>'स्टक पहिले नै शून्य छ।'],
    'stock_updated_ok'         => ['en'=>'Stock updated successfully.','np'=>'स्टक सफलतापूर्वक अद्यावधिक भयो।'],
    'stock_updated_fail'       => ['en'=>'Stock update failed.','np'=>'स्टक अद्यावधिक असफल भयो।'],
    'network_error'            => ['en'=>'Network error. Please try again.','np'=>'नेटवर्क त्रुटि। फेरि प्रयास गर्नुहोस्।'],
    'manage_profile_desc'      => ['en'=>'Manage your profile information.','np'=>'तपाईंको प्रोफाइल जानकारी व्यवस्थापन गर्नुहोस्।'],
    'chef'                     => ['en'=>'Chef','np'=>'शेफ'],
    'your_restaurant'          => ['en'=>'Your Restaurant','np'=>'तपाईंको रेस्टुरेन्ट'],
    'kyc_restaurant_message'   => ['en'=>'Complete KYC to unlock restaurant features.','np'=>'रेस्टुरेन्ट सुविधाहरू खोल्न KYC पूरा गर्नुहोस्।'],
    'no_tables_yet'            => ['en'=>'No tables yet.','np'=>'अझै कुनै टेबल छैन।'],
    'role_placeholder'         => ['en'=>'Select role','np'=>'भूमिका छान्नुहोस्'],
    'kyc_transport_message'    => ['en'=>'Complete KYC to unlock transport features.','np'=>'यातायात सुविधाहरू खोल्न KYC पूरा गर्नुहोस्।'],
    'no_vehicles_registered'   => ['en'=>'No vehicles registered yet.','np'=>'अझै कुनै सवारी दर्ता भएको छैन।'],
    'documents_uploaded'       => ['en'=>'Documents uploaded','np'=>'कागजातहरू अपलोड भए'],

    // Reports / Price History
    'price_history_title'       => ['en'=>'Price History','np'=>'मूल्य इतिहास'],
    'price_history_desc'        => ['en'=>'Audit trail of all room price changes','np'=>'सबै कोठा मूल्य परिवर्तनको अडिट ट्रेल'],
    'price_history_change_count'=> ['en'=>'change(s) recorded','np'=>'परिवर्तन(हरू) रेकर्ड भए'],
    'back_to_reports'           => ['en'=>'Back to Reports','np'=>'रिपोर्टहरूमा फर्कनुहोस्'],
    'ai_suggested_rate_applied' => ['en'=>'AI suggested rate applied','np'=>'AI सुझाव दर लागू भयो'],
    'ai_unavailable_baseline'   => ['en'=>'AI unavailable — baseline','np'=>'AI अनुपलब्ध — आधाररेखा'],
    'ai_baseline_unavailable'   => ['en'=>'Baseline (AI unavailable)','np'=>'आधाररेखा (AI अनुपलब्ध)'],
    'current_occupancy'         => ['en'=>'Current','np'=>'हालको'],
    'rates_confirmed_note'      => ['en'=>'Rates confirmed or adjusted within ±10%','np'=>'दरहरू ±१०% भित्र पुष्टि वा समायोजित'],
    'hold_rate_recommended'     => ['en'=>'Hold rate (no change recommended)','np'=>'दर होल्ड (परिवर्तन सिफारिस नै)'],
    'no_bookings_discount'      => ['en'=>'No bookings 30d — discount recommended','np'=>'३० दिनमा कुनै बुकिङ नै — छुट सिफारिस'],
    'moderate_occupancy_increase'=>['en'=>'Moderate occupancy — rate increase','np'=>'मध्यम अधिभोग — दर वृद्धि'],
    'clamped_min'               => ['en'=>'clamped to minimum','np'=>'न्यूनतममा सीमित'],
    'history_label'             => ['en'=>'History','np'=>'इतिहास'],
    'top_rooms_label'           => ['en'=>'Top Rooms by Revenue','np'=>'आम्दानी अनुसार शीर्ष कोठाहरू'],

    // SOS
    'siren_active'        => ['en'=>'Siren active — click Silence to stop','np'=>'साइरन सक्रिय — बन्द गर्न Silence क्लिक गर्नुहोस्'],
    'silence_acknowledge' => ['en'=>'Silence & Acknowledge','np'=>'चुप र स्वीकार गर्नुहोस्'],

    // Suppliers
    'suppliers_desc'      => ['en'=>'Manage your suppliers (stock, materials, vendors).','np'=>'तपाईंका आपूर्तिकर्ताहरू (स्टक, सामग्री, विक्रेता) व्यवस्थापन गर्नुहोस्।'],
    'supplier_name_label' => ['en'=>'Name','np'=>'नाम'],
    'save_supplier'       => ['en'=>'Save Supplier','np'=>'आपूर्तिकर्ता सुरक्षित गर्नुहोस्'],
    'update_supplier'     => ['en'=>'Update Supplier','np'=>'आपूर्तिकर्ता अद्यावधिक'],

    // Housekeeping
    'clean_room'         => ['en'=>'Clean Room','np'=>'कोठा सफा गर्नुहोस्'],
    'no_specific_room'   => ['en'=>'— No specific room —','np'=>'— कुनै विशेष कोठा नै —'],
    'task_template_clean'=> ['en'=>'Clean Room','np'=>'कोठा सफा'],

    // Ads
    'ad_content_section'   => ['en'=>'Ad Content','np'=>'विज्ञापन सामग्री'],
    'link_cta_section'     => ['en'=>'Link & Call-to-Action','np'=>'लिंक र कल-टु-एक्शन'],
    'targeting_section'    => ['en'=>'Targeting','np'=>'लक्ष्यीकरण'],
    'transaction_id_opt'   => ['en'=>'Transaction ID (optional)','np'=>'लेनदेन ID (वैकल्पिक)'],
    'choose_payment_method'=> ['en'=>'Choose payment method','np'=>'भुक्तानी विधि छान्नुहोस्'],
    'placehold_slug'       => ['en'=>'/explore/service/your-trek-slug','np'=>'/explore/service/तपाईंको-ट्रेक-स्लग'],
    'placehold_ebc'        => ['en'=>'e.g. Everest Base Camp','np'=>'जस्तै: सगरमाथा आधार शिविर'],

    // Verification
    'file_types_max' => ['en'=>'PDF, JPG, PNG (max 5MB)','np'=>'PDF, JPG, PNG (अधिकतम ५MB)'],

    // Services
    'service_count_label' => ['en'=>'service(s)','np'=>'सेवा(हरू)'],
    'category_hotel'      => ['en'=>'Hotel','np'=>'होटल'],
    'placehold_price'     => ['en'=>'e.g. 5000','np'=>'जस्तै: ५०००'],

    // Payment methods
    'file_size_hint'   => ['en'=>'PNG / JPG, max 2MB','np'=>'PNG / JPG, अधिकतम २MB'],
    'placehold_bank'   => ['en'=>'e.g. NIC Asia Bank','np'=>'जस्तै: नाइस एशिया बैंक'],
];

// ═══════════════════════════════════════════════════════════════
// CROSS-CUTTING KEYS (5 locales)
// ═══════════════════════════════════════════════════════════════
$crossKeys = [
    // Status enums
    'st_pending'     => ['en'=>'Pending','np'=>'पेन्डिङ','hi'=>'लंबित','zh'=>'待处理','ru'=>'В ожидании'],
    'st_confirmed'   => ['en'=>'Confirmed','np'=>'पुष्टि भयो','hi'=>'पुष्टि हुआ','zh'=>'已确认','ru'=>'Подтверждено'],
    'st_checked_in'  => ['en'=>'Checked In','np'=>'चेक-इन भयो','hi'=>'चेक-इन हुआ','zh'=>'已入住','ru'=>'Заселен'],
    'st_checked_out' => ['en'=>'Checked Out','np'=>'चेक-आउट भयो','hi'=>'चेक-आउट हुआ','zh'=>'已退房','ru'=>'Выселен'],
    'st_cancelled'   => ['en'=>'Cancelled','np'=>'रद्द भयो','hi'=>'रद्द हुआ','zh'=>'已取消','ru'=>'Отменено'],
    'st_delivered'   => ['en'=>'Delivered','np'=>'पुर्‍याइयो','hi'=>'डिलीवर हुआ','zh'=>'已送达','ru'=>'Доставлено'],
    'st_shipped'     => ['en'=>'Shipped','np'=>'पठाइयो','hi'=>'भेजा गया','zh'=>'已发货','ru'=>'Отправлено'],
    'st_processing'  => ['en'=>'Processing','np'=>'प्रक्रियामा','hi'=>'प्रोसेसिंग','zh'=>'处理中','ru'=>'Обработка'],
    'st_refunded'    => ['en'=>'Refunded','np'=>'रिफन्ड भयो','hi'=>'रिफंड हुआ','zh'=>'已退款','ru'=>'Возвращено'],
    'st_picked_up'   => ['en'=>'Picked Up','np'=>'उठाइयो','hi'=>'उठाया','zh'=>'已取货','ru'=>'Забрано'],
    'st_returned'    => ['en'=>'Returned','np'=>'फिर्ता भयो','hi'=>'लौटाया','zh'=>'已退回','ru'=>'Возвращено'],
    'st_overdue'     => ['en'=>'Overdue','np'=>'म्याद नाघ्यो','hi'=>'अतिदेय','zh'=>'逾期','ru'=>'Просрочено'],
    'st_paid'        => ['en'=>'Paid','np'=>'भुक्तानी भयो','hi'=>'भुगतान हुआ','zh'=>'已支付','ru'=>'Оплачено'],
    'st_failed'      => ['en'=>'Failed','np'=>'असफल','hi'=>'विफल','zh'=>'失败','ru'=>'Ошибка'],
    'st_active'      => ['en'=>'Active','np'=>'सक्रिय','hi'=>'सक्रिय','zh'=>'活跃','ru'=>'Активный'],
    'st_inactive'    => ['en'=>'Inactive','np'=>'निष्क्रिय','hi'=>'निष्क्रिय','zh'=>'未激活','ru'=>'Неактивный'],
    'st_low'         => ['en'=>'Low','np'=>'न्यून','hi'=>'कम','zh'=>'低','ru'=>'Низкий'],
    'st_queue'       => ['en'=>'Queue','np'=>'कतार','hi'=>'कतार','zh'=>'队列','ru'=>'Очередь'],
    'st_done'        => ['en'=>'Done','np'=>'सम्पन्न','hi'=>'पूर्ण','zh'=>'已完成','ru'=>'Готово'],
    'st_in_progress' => ['en'=>'In Progress','np'=>'प्रगतिमा','hi'=>'प्रगति पर','zh'=>'进行中','ru'=>'В процессе'],
    'st_available'   => ['en'=>'Available','np'=>'उपलब्ध','hi'=>'उपलब्ध','zh'=>'可用','ru'=>'Доступно'],
    'st_occupied'    => ['en'=>'Occupied','np'=>'भरिएको','hi'=>'अधिकृत','zh'=>'已占用','ru'=>'Занято'],
    'st_reserved'    => ['en'=>'Reserved','np'=>'आरक्षित','hi'=>'आरक्षित','zh'=>'已预订','ru'=>'Зарезервировано'],
    'st_dirty'       => ['en'=>'Dirty','np'=>'फोहोर','hi'=>'गंदा','zh'=>'脏','ru'=>'Грязный'],
    'st_maintenance' => ['en'=>'Maintenance','np'=>'मर्मत','hi'=>'रखरखाव','zh'=>'维护','ru'=>'Обслуживание'],
    'st_cleaning'    => ['en'=>'Cleaning','np'=>'सफाई','hi'=>'सफाई','zh'=>'清洁','ru'=>'Уборка'],
    'st_free'        => ['en'=>'Free','np'=>'खाली','hi'=>'खाली','zh'=>'空闲','ru'=>'Свободно'],
    // Room types
    'rt_standard' => ['en'=>'Standard','np'=>'स्ट्यान्डर्ड','hi'=>'मानक','zh'=>'标准','ru'=>'Стандарт'],
    'rt_deluxe'   => ['en'=>'Deluxe','np'=>'डिलक्स','hi'=>'डीलक्स','zh'=>'豪华','ru'=>'Люкс'],
    'rt_suite'    => ['en'=>'Suite','np'=>'सुइट','hi'=>'सुइट','zh'=>'套房','ru'=>'Люкс'],
    'rt_superior' => ['en'=>'Superior','np'=>'सुपीरियर','hi'=>'सुपीरियर','zh'=>'高级','ru'=>'Превосходный'],
    'rt_premium'  => ['en'=>'Premium','np'=>'प्रिमियम','hi'=>'प्रीमियम','zh'=>'高级','ru'=>'Премиум'],
    // Days
    'day_sun' => ['en'=>'Sun','np'=>'आइत','hi'=>'रवि','zh'=>'周日','ru'=>'Вс'],
    'day_mon' => ['en'=>'Mon','np'=>'सोम','hi'=>'सोम','zh'=>'周一','ru'=>'Пн'],
    'day_tue' => ['en'=>'Tue','np'=>'मंगल','hi'=>'मंगल','zh'=>'周二','ru'=>'Вт'],
    'day_wed' => ['en'=>'Wed','np'=>'बुध','hi'=>'बुध','zh'=>'周三','ru'=>'Ср'],
    'day_thu' => ['en'=>'Thu','np'=>'बिही','hi'=>'गुरु','zh'=>'周四','ru'=>'Чт'],
    'day_fri' => ['en'=>'Fri','np'=>'शुक्र','hi'=>'शुक्र','zh'=>'周五','ru'=>'Пт'],
    'day_sat' => ['en'=>'Sat','np'=>'शनि','hi'=>'शनि','zh'=>'周六','ru'=>'Сб'],

    // Common UI (5 locales — public/traveler/wholesale पनि चाहिन्छ)
    'common_no_file_chosen' => ['en'=>'No file chosen','np'=>'कुनै फाइल छानिएको छैन','hi'=>'कोई फ़ाइल नहीं चुनी','zh'=>'未选择文件','ru'=>'Файл не выбран'],
    'common_choose_file'    => ['en'=>'Choose File','np'=>'फाइल छान्नुहोस्','hi'=>'फ़ाइल चुनें','zh'=>'选择文件','ru'=>'Выбрать файл'],
    'common_select'         => ['en'=>'Select','np'=>'छान्नुहोस्','hi'=>'चुनें','zh'=>'选择','ru'=>'Выбрать'],
    'common_optional'       => ['en'=>'(optional)','np'=>'(वैकल्पिक)','hi'=>'(वैकल्पिक)','zh'=>'(可选)','ru'=>'(необязательно)'],
    'common_learn_more'     => ['en'=>'Learn More','np'=>'अझै जान्नुहोस्','hi'=>'और जानें','zh'=>'了解更多','ru'=>'Узнать больше'],
    'common_date_placeholder'=>['en'=>'mm/dd/yyyy','np'=>'dd/mm/yyyy','hi'=>'dd/mm/yyyy','zh'=>'yyyy-mm-dd','ru'=>'dd.mm.yyyy'],

    // My Bookings / Orders / RFQs (traveler pages — 5 locales)
    'my_bookings_page_title' => ['en'=>'My Bookings','np'=>'मेरा बुकिङहरू','hi'=>'मेरी बुकिंग्स','zh'=>'我的预订','ru'=>'Мои бронирования'],
    'my_bookings_page_desc'  => ['en'=>'All your activity across TravelAI Nepal','np'=>'TravelAI नेपालमा तपाईंका सबै गतिविधिहरू','hi'=>'TravelAI नेपाल में आपकी सभी गतिविधियाँ','zh'=>'您在 TravelAI Nepal 的所有活动','ru'=>'Вся ваша активность в TravelAI Nepal'],
    'tab_all'                => ['en'=>'All','np'=>'सबै','hi'=>'सभी','zh'=>'全部','ru'=>'Все'],
    'tab_treks'              => ['en'=>'Treks','np'=>'ट्रेकहरू','hi'=>'ट्रेक','zh'=>'徒步','ru'=>'Треки'],
    'tab_shop'               => ['en'=>'Shop','np'=>'पसल','hi'=>'दुकान','zh'=>'商店','ru'=>'Магазин'],
    'tab_rental'             => ['en'=>'Rental','np'=>'भाडा','hi'=>'किराया','zh'=>'租赁','ru'=>'Аренда'],
    'tab_wholesale'          => ['en'=>'Wholesale','np'=>'थोक','hi'=>'थोक','zh'=>'批发','ru'=>'Опт'],
    'tab_rfqs'               => ['en'=>'RFQs','np'=>'RFQहरू','hi'=>'RFQs','zh'=>'询价','ru'=>'Запросы'],
    'my_orders_desc'         => ['en'=>'All your product orders','np'=>'तपाईंका सबै उत्पादन अर्डरहरू','hi'=>'आपके सभी उत्पाद ऑर्डर','zh'=>'您的所有产品订单','ru'=>'Все ваши заказы'],
    'no_orders_count'        => ['en'=>'0 orders','np'=>'० अर्डरहरू','hi'=>'० ऑर्डर','zh'=>'0 订单','ru'=>'0 заказов'],

    // Wholesale
    'adjust_filters_msg' => ['en'=>'Try adjusting your filters or search terms.','np'=>'तपाईंका फिल्टर वा खोज शब्दहरू समायोजन गर्नुहोस्।','hi'=>'अपने फ़िल्टर या खोज शब्दों को समायोजित करें।','zh'=>'请调整筛选条件或搜索词。','ru'=>'Измените фильтры или поисковые запросы.'],
];

// ═══ Add provider keys (en/np मात्र) ═══
echo "─── Provider-only keys (EN/NP) ───\n";
foreach (['en', 'np'] as $loc) {
    $path = "resources/lang/{$loc}/messages.php";
    if (!file_exists($path)) { echo "  ⚠️  {$path} missing\n"; continue; }
    copy($path, $path . '.bak_keys');
    $data = require $path;
    $added = 0;
    foreach ($providerKeys as $key => $pair) {
        if (!isset($data[$key]) && isset($pair[$loc])) {
            $data[$key] = $pair[$loc];
            $added++;
        }
    }
    if ($added === 0) { unlink($path . '.bak_keys'); echo "  ⏭  {$loc}: all present\n"; continue; }
    $new = "<?php\n\nreturn " . var_export($data, true) . ";\n";
    [$ok, $out] = phpLint($new);
    if (!$ok) { copy($path . '.bak_keys', $path); echo "  ❌ {$loc}: lint fail\n$out\n"; continue; }
    file_put_contents($path, $new);
    echo "  ✅ {$loc}: +{$added} provider keys\n";
}

// ═══ Add cross-cutting keys (5 locales) ═══
echo "\n─── Cross-cutting keys (5 locales) ───\n";
foreach (['en', 'np', 'hi', 'zh', 'ru'] as $loc) {
    $path = "resources/lang/{$loc}/messages.php";
    if (!file_exists($path)) { echo "  ⚠️  {$path} missing\n"; continue; }
    copy($path, $path . '.bak_cross');
    $data = require $path;
    $added = 0;
    foreach ($crossKeys as $key => $pair) {
        if (!isset($data[$key]) && isset($pair[$loc])) {
            $data[$key] = $pair[$loc];
            $added++;
        }
    }
    if ($added === 0) { unlink($path . '.bak_cross'); echo "  ⏭  {$loc}: all present\n"; continue; }
    $new = "<?php\n\nreturn " . var_export($data, true) . ";\n";
    [$ok, $out] = phpLint($new);
    if (!$ok) { copy($path . '.bak_cross', $path); echo "  ❌ {$loc}: lint fail\n$out\n"; continue; }
    file_put_contents($path, $new);
    echo "  ✅ {$loc}: +{$added} cross keys\n";
}

// ═══ VERIFY ═══
echo "\n═══ VERIFY ═══\n";
echo "\n  Provider keys (EN/NP only):\n";
$en = require 'resources/lang/en/messages.php';
$np = require 'resources/lang/np/messages.php';
$missP = 0;
foreach (array_keys($providerKeys) as $k) {
    if (!isset($en[$k]) || !isset($np[$k])) $missP++;
}
echo "    Missing: $missP / " . count($providerKeys) . " " . ($missP === 0 ? "✅" : "❌") . "\n";

echo "\n  Cross-cutting keys (5 locales):\n";
foreach (['en', 'np', 'hi', 'zh', 'ru'] as $loc) {
    $path = "resources/lang/{$loc}/messages.php";
    if (!file_exists($path)) continue;
    $d = require $path;
    $miss = 0;
    foreach (array_keys($crossKeys) as $k) if (!isset($d[$k])) $miss++;
    echo "    {$loc}: " . ($miss === 0 ? "✅ all " . count($crossKeys) : "❌ missing $miss") . "\n";
}

echo "\n  Previous unresolved keys (19):\n";
$prevMissing = ['artist','no_artworks_filter','no_custom_orders_yet','unassigned','inventory_dashboard_title','kyc_inventory_message','stock_already_zero','stock_updated_ok','stock_updated_fail','network_error','manage_profile_desc','chef','your_restaurant','kyc_restaurant_message','no_tables_yet','role_placeholder','kyc_transport_message','no_vehicles_registered','documents_uploaded'];
$prev = 0;
foreach ($prevMissing as $k) if (!isset($en[$k])) $prev++;
echo "    Still missing: $prev / 19 " . ($prev === 0 ? "✅" : "❌") . "\n";

echo "\n  Total keys added (approx):\n";
echo "    Provider (×2): " . (count($providerKeys) * 2) . "\n";
echo "    Cross (×5):    " . (count($crossKeys) * 5) . "\n";
echo "    Grand total:   " . ((count($providerKeys) * 2) + (count($crossKeys) * 5)) . " key entries\n";

echo "\n✅ DONE — ALL KEYS ADDED\n";
echo "\nCleanup:\n";
echo "for %f in (resources\\lang\\en\\messages.php.bak_keys resources\\lang\\np\\messages.php.bak_keys resources\\lang\\en\\messages.php.bak_cross resources\\lang\\np\\messages.php.bak_cross resources\\lang\\hi\\messages.php.bak_cross resources\\lang\\zh\\messages.php.bak_cross resources\\lang\\ru\\messages.php.bak_cross) do @del %f 2>nul\n";