<?php
$databaseFile = __DIR__ . '/data/shop.db';
$pdo = new PDO('sqlite:' . $databaseFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS favorites (id INTEGER PRIMARY KEY AUTOINCREMENT, product_id TEXT UNIQUE, title TEXT, image TEXT, created_at TEXT)");
$pdo->exec("CREATE TABLE IF NOT EXISTS carts (id INTEGER PRIMARY KEY AUTOINCREMENT, product_id TEXT UNIQUE, title TEXT, image TEXT, quantity INTEGER, created_at TEXT)");
$pdo->exec("CREATE TABLE IF NOT EXISTS clicks (id INTEGER PRIMARY KEY AUTOINCREMENT, product_id TEXT, title TEXT, image TEXT, action TEXT, created_at TEXT)");

$products = [
    ['id' => 'p1', 'title' => '極簡現代座椅', 'image' => 'https://img.pchome.com.tw/cs/items/DQCE0LA900JFDTW/l000001_1762412914.jpg?width=640', 'price' => 'NT$2,980'],
    ['id' => 'p2', 'title' => '設計師落地燈', 'image' => 'https://forgemind.net/media/wp-content/uploads/2021/02/11614393328.jpg', 'price' => 'NT$1,680'],
    ['id' => 'p3', 'title' => '霧面陶瓷花瓶', 'image' => 'https://thumbnail.coupangcdn.com/thumbnails/remote/492x492ex/image/rs_quotation_api/qj4opjlc/f599e0b2296c489895e27b9ede6ad63b.jpg', 'price' => 'NT$890'],
    ['id' => 'p4', 'title' => '北歐風抱枕', 'image' => 'https://images.buy123.com.tw/deal/1376739173897079931-7TRH3CO8.png', 'price' => 'NT$540'],
    ['id' => 'p5', 'title' => '極致床品組', 'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1000&q=80', 'price' => 'NT$3,280'],
    ['id' => 'p6', 'title' => '手工木質餐桌', 'image' => 'https://laoshihfu.tw/wp-content/uploads/2024/09/IMG_6233.jpg', 'price' => 'NT$6,480'],
    ['id' => 'p8', 'title' => '極光掛畫', 'image' => 'https://d2onjhd726mt7c.cloudfront.net/online_images/art_img/big/36097.jpg', 'price' => 'NT$1,250'],
    ['id' => 'p7', 'title' => '復古攝影機', 'image' => 'https://tfai.openmuseum.tw/files/muse_tfi/muse_styles/thumbnail_medium/mcode/ca4e493e20477727f413272560a80fd5.jpg?itok=Dmq8EH5q', 'price' => 'NT$1,990'],
    ['id' => 'p9', 'title' => '綠意盆栽組', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRLc-xDG8RgCAxB6JmY6A9uhBJFrgK-MkH0xw&s', 'price' => 'NT$760'],
    ['id' => 'p10', 'title' => '工業風置物架', 'image' => 'https://www.mit-machining.com/store_image/turnshelf/Q1690274416891.jpg', 'price' => 'NT$2,150'],
    ['id' => 'p11', 'title' => '大理石餐盤組', 'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSkoXTAsoW-WLU89y4lhq1ajzHvA6Za9GukVw&s', 'price' => 'NT$1,120'],
    ['id' => 'p12', 'title' => '香氛蠟燭組', 'image' => 'https://media.etmall.com.tw/nximg/003160/3160983/3160983_xxl.jpg?t=18132382068', 'price' => 'NT$620'],
];
$topSellers = array_slice($products, 0, 6);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['action'])) {
    $productId = $_POST['product_id'];
    $action = $_POST['action'];
    $product = array_values(array_filter($products, fn($item) => $item['id'] === $productId));
    if ($product) {
        $item = $product[0];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        if ($action === 'favorite') {
            $stmt = $pdo->prepare('SELECT id FROM favorites WHERE product_id = :product_id');
            $stmt->execute([':product_id' => $item['id']]);
            $existingFavorite = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existingFavorite) {
                $stmt = $pdo->prepare('DELETE FROM favorites WHERE product_id = :product_id');
                $stmt->execute([':product_id' => $item['id']]);
                $message = '已從我的最愛移除：' . htmlspecialchars($item['title'], ENT_QUOTES);
                $clickAction = 'favorite_remove';
            } else {
                $stmt = $pdo->prepare('INSERT INTO favorites (product_id, title, image, created_at) VALUES (:product_id, :title, :image, :created_at)');
                $stmt->execute([':product_id' => $item['id'], ':title' => $item['title'], ':image' => $item['image'], ':created_at' => $now]);
                $message = '已加入我的最愛：' . htmlspecialchars($item['title'], ENT_QUOTES);
                $clickAction = 'favorite';
            }
            $stmt = $pdo->prepare('INSERT INTO clicks (product_id, title, image, action, created_at) VALUES (:product_id,:title,:image,:action,:created_at)');
            $stmt->execute([':product_id'=>$item['id'], ':title'=>$item['title'], ':image'=>$item['image'], ':action'=>$clickAction, ':created_at'=>$now]);
        }

        if ($action === 'cart') {
            $stmt = $pdo->prepare('SELECT id FROM carts WHERE product_id = :product_id');
            $stmt->execute([':product_id' => $item['id']]);
            $existingCart = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existingCart) {
                $stmt = $pdo->prepare('DELETE FROM carts WHERE product_id = :product_id');
                $stmt->execute([':product_id' => $item['id']]);
                $message = '已從購物車移除：' . htmlspecialchars($item['title'], ENT_QUOTES);
                $clickAction = 'cart_remove';
            } else {
                $stmt = $pdo->prepare('INSERT INTO carts (product_id, title, image, quantity, created_at) VALUES (:product_id, :title, :image, 1, :created_at)');
                $stmt->execute([':product_id' => $item['id'], ':title' => $item['title'], ':image' => $item['image'], ':created_at' => $now]);
                $message = '已加入購物車：' . htmlspecialchars($item['title'], ENT_QUOTES);
                $clickAction = 'cart';
            }
            $stmt = $pdo->prepare('INSERT INTO clicks (product_id, title, image, action, created_at) VALUES (:product_id,:title,:image,:action,:created_at)');
            $stmt->execute([':product_id'=>$item['id'], ':title'=>$item['title'], ':image'=>$item['image'], ':action'=>$clickAction, ':created_at'=>$now]);
        }
    }
}

$favorites = $pdo->query('SELECT * FROM favorites ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$carts = $pdo->query('SELECT * FROM carts ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$clicks = $pdo->query('SELECT * FROM clicks ORDER BY created_at DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
$favoriteIds = array_column($favorites, 'product_id');
$cartIds = array_column($carts, 'product_id');
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>前端商品畫廊與 PHP 購物車</title>
    <style>
        :root {
            color-scheme: light;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f6f7fb;
            color: #1c1f2d;
            --card-radius: 24px;
            --shadow: 0 24px 60px rgba(35, 40, 61, 0.12);
            --accent: #ff6b81;
            --accent-soft: #ffe0e7;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #f8fbff 0%, #eef3ff 100%);
        }
        header {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(28,31,45,.08);
        }
        .hero {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
        }
        .hero h1 {
            margin: 0;
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            line-height: 1.05;
        }
        .hero p {
            width: 798px;
            margin: .75rem 0 0;
           /* max-width: 100rem;
            font-size: clamp(1.35rem, 2vw, 1.5rem);
            color: #5f6478;*/
            font-size: 18px;
            
        }
        .nav-carousel {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(280px, auto);
            gap: 1rem;
            overflow-x: auto;
            padding: 1rem 2rem 1.25rem;
            scroll-snap-type: x mandatory;
        }
        .nav-carousel::-webkit-scrollbar { display: none; }
        .nav-item {
            min-width: 200px;
            scroll-snap-align: start;
            padding: 1rem 1.2rem;
            background: #fff;
            border-radius: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-item {
            display: grid;
            grid-template-columns: 72px 1fr;
            align-items: center;
            gap: 0.85rem;
        }
        .nav-item img.nav-thumb {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: cover;
            box-shadow: 0 14px 32px rgba(62, 71, 107, 0.14);
        }
        .nav-item strong { display: block; font-size: 0.95rem; }
        .nav-item span { color: #6b7280; font-size: 0.85rem; }
        main {
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            max-width: 1300px;
            margin: 0 auto 3rem;
        }
        .card {
            position: relative;
            overflow: hidden;
            border-radius: var(--card-radius);
            background: #fff;
            box-shadow: var(--shadow);
            transform: translateZ(0);
        }
        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.35));
            pointer-events: none;
        }
        .card-image {
            position: relative;
            overflow: hidden;
        }
        .card-image img {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            transition: transform .35s ease, filter .35s ease;
            transition: opacity 0.6s ease-in-out; /* 設定透明度變化的轉場效果 */
        }
        .card:hover img {
            transform: scale(1.08);
            filter: blur(1px) brightness(1.02);
        }
        .icon-button {
            position: absolute;
            width: 2.6rem;
            height: 2.6rem;
            border: none;
            border-radius: 50%;
            display: grid;
            place-items: center;
            cursor: pointer;
            background: rgba(255,255,255,0.9);
            color: #1c1f2d;
            transition: transform .25s ease, background .25s ease, color .25s ease;
        }
        .icon-button:hover { transform: scale(1.08); background: #fff; }
        .icon-button.active { background: #dbddfa; color: #fff; }
        .icon-button.favorite { top: 1rem; right: 1rem; }
        .icon-button.cart { bottom: 1rem; right: 1rem; }
        .card-body {
            position: relative;
            padding: 1.35rem 1.35rem 1.6rem;
        }
        .card-body h2 {
            margin: 0 0 .55rem;
            font-size: 1.1rem;
        }
        .card-body p {
            margin: 0;
            color: #6b7280;
            font-size: 0.95rem;
        }
        footer {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 2rem 3rem;
        }
        .summary {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .panel {
            background: #fff;
            padding: 1.5rem;
            border-radius: 24px;
            box-shadow: var(--shadow);
        }
        .panel h3 {
            margin: 0 0 1rem;
            font-size: 1.05rem;
        }
        .chip {
            width: 25%;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 999px;
            background: #eef2ff;
            color: #3739a7;
            font-size: .95rem;
            margin: 0 0 .75rem 0;
        }
        
        .list-item {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 0.9rem;
            align-items: center;
            padding: .65rem 0;
            border-bottom: 1px solid #f1f3f8;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item img {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 18px;
        }
        .list-item strong { font-size: .98rem; display: block; margin-bottom: .2rem; }
        .pulse {
            animation: pulse 4s infinite;
        }
        @keyframes pulse {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

       
    </style>
 <script>//輪播的速度調快一點,每隔3秒換一次圖片(即一整組).
        const carousel = document.getElementById('bestSellers');
        let scrollLeft = 0;
        const step = 260;
        setInterval(() => {
            if (!carousel) return;
            const maxScroll = carousel.scrollWidth - carousel.clientWidth;
            if (maxScroll <= 0) return;
            scrollLeft += step;
            if (scrollLeft > maxScroll) {
                scrollLeft = 0;
            }
            carousel.scrollTo({ left: scrollLeft, behavior: 'smooth' });
        }, 3000);
    </script>
</head>
<body>
    <header>
        <div class="hero">
            <div>
                <h1>最新網頁設計 / CSS Grid / sticky / absolute</h1>                
                <p class="chip">12 件購物商品(以PHP購物車示範)：以position,absolute,sticky,hover特效，在點擊愛心/購物車後,透過PHP+SQLite存入或刪除資料。</p>                
            </div>
            <div class="chip">已加入我的最愛：<?php echo count($favorites); ?> 件 / 購物車：<?php echo array_sum(array_column($carts, 'quantity')); ?> 件</div>
        </div>
        
        <div class="nav-carousel" id="bestSellers">
            <?php foreach ($topSellers as $index => $seller): ?>
                <div class="nav-item<?php echo $index === 0 ? ' pulse' : ''; ?>">
                    <img src="<?php echo $seller['image']; ?>" alt="<?php echo htmlspecialchars($seller['title'], ENT_QUOTES); ?>" class="nav-thumb" loading="lazy" />
                    <div>
                        <strong><?php echo htmlspecialchars($seller['title'], ENT_QUOTES); ?></strong>
                        <span>熱銷商品</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </header>
    <?php if ($message): ?>
        <div style="max-width:1300px;margin:1rem auto; padding:1rem 2rem; background:#fdf2f8; border:1px solid #fbcfe8; color:#be185d; border-radius:18px;">
            <?php echo htmlspecialchars($message, ENT_QUOTES); ?>
        </div>
    <?php endif; ?>
    <main>
        <?php foreach ($products as $product): ?>
            <article class="card">
                <div class="card-image">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title'], ENT_QUOTES); ?>" loading="lazy" />
                    <form method="post" class="icon-button favorite<?php echo in_array($product['id'], $favoriteIds) ? ' active' : ''; ?>" style="position:absolute;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="favorite">
                        <button type="submit" title="加入我的最愛" style="background:transparent;border:none;color:inherit;cursor:pointer;">
                            <?php echo in_array($product['id'], $favoriteIds) ? '❤️' : '🤍'; ?>
                        </button>
                    </form>
                    <form method="post" class="icon-button cart<?php echo in_array($product['id'], $cartIds) ? ' active' : ''; ?>" style="position:absolute;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="cart">
                        <button type="submit" title="加入購物車" style="background:transparent;border:none;color:inherit;cursor:pointer;">🛒</button>
                    </form>
                </div>
                <div class="card-body">
                    <h2><?php echo htmlspecialchars($product['title'], ENT_QUOTES); ?></h2>
                    <p><?php echo $product['price']; ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </main>
    <footer>
        <div class="summary">
            <section class="panel">
                <h3>我的最愛</h3>
                <?php if ($favorites): ?>
                    <?php foreach ($favorites as $favorite): ?>
                        <div class="list-item">
                            <img src="<?php echo $favorite['image']; ?>" alt="<?php echo htmlspecialchars($favorite['title'], ENT_QUOTES); ?>">
                            <div>
                                <strong><?php echo htmlspecialchars($favorite['title'], ENT_QUOTES); ?></strong>
                                <span>加入時間：<?php echo $favorite['created_at']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>目前尚未加入任何最愛。</p>
                <?php endif; ?>
            </section>
            <section class="panel">
                <h3>購物車清單</h3>
                <?php if ($carts): ?>
                    <?php foreach ($carts as $item): ?>
                        <div class="list-item">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>">
                            <div>
                                <strong><?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?></strong>
                                <span>數量：<?php echo $item['quantity']; ?> / 加入時間：<?php echo $item['created_at']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>購物車目前是空的。</p>
                <?php endif; ?>
            </section>
        </div>
        <section class="panel" style="margin-top:1.5rem;">
            <h3>最近點擊紀錄</h3>
            <?php if ($clicks): ?>
                <?php foreach ($clicks as $click): ?>
                    <div class="list-item">
                        <img src="<?php echo $click['image']; ?>" alt="<?php echo htmlspecialchars($click['title'], ENT_QUOTES); ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($click['title'], ENT_QUOTES); ?></strong>
                            <span>動作：<?php echo $click['action']; ?> / <?php echo $click['created_at']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>尚無最近點擊紀錄。</p>
            <?php endif; ?>
        </section>
    </footer>
   
</body>
</html>
