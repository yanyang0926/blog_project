<?php
// 设置 blog 目录路径
$blogDir = 'blog/';

// 检查目录是否存在
if (!is_dir($blogDir)) {
    die("<div class='list-item'><p>错误：目录 '$blogDir' 不存在</p></div>");
}

// 获取目录中所有的 txt 文件并按修改时间排序（最新的在前）
$txtFiles = glob($blogDir . '*.txt');
usort($txtFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一何博客</title>
    <!-- 预加载关键资源 -->
    <link rel="preload" href="res/a.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preconnect" href="https://example.com">
    <style>
        /* 字体定义 - 优化版本 */
        @font-face {
            font-family: 'CustomFont';
            src: local('Microsoft YaHei'),
                 url('res/a.ttf') format('truetype');
            font-weight: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'CustomFont';
            src: local('Microsoft YaHei Bold'),
                 url('res/a.ttf') format('truetype');
            font-weight: bold;
            font-display: swap;
        }

        /* 基础样式 */
        body {
            font-family: 'CustomFont', 'Microsoft YaHei', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FFFFFF;
        }

        /* 顶部容器 */
        .header-container {
            position: relative;
            height: 60px;
            margin: 40px 0;
        }

        /* 黑框容器 */
        .black-box-wrapper {
            position: absolute;
            top: 10px;
            right: 30px;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        /* 黑框样式 */
        .black-box {
            width: 160px;
            height: 70px;
            background-color: #000000;
            margin-left: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 3px;
            position: relative;
        }

        /* 三个视图的样式 */
        .black-box-view {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #EEEEEE;
            font-size: 16px;
            line-height: 1.5;
        }

        /* 视图分隔线 */
        .view-divider {
            width: 1px;
            height: 60%;
            background-color: rgba(255,255,255,0.3);
        }

        /* 图片容器 */
        .image-content {
            position: absolute;
            left: 30px;
            display: flex;
            align-items: center;
            z-index: 2;
        }

        /* 图片样式 */
        .top-image {
            width: 80px;
            height: 80px;
        }
        .top-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 4px;
        }

        /* 内容区域 */
        .content-list {
            padding: 0 35px 0 38px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* 文章条目 */
        .list-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            word-wrap: break-word;
        }

        /* 标题样式 */
        .item-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #000000;
            margin-top: 8px;
            word-break: break-word;
        }

        /* 内容样式 */
        .item-content {
            font-size: 13px;
            color: #000000;
            margin-bottom: 5px;
            line-height: 1.5;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 阅读更多链接 */
        .read-more {
            color: #800020;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
            margin-top: 5px;
        }

        /* 分割线 */
        .divider {
            color: #000;
            font-weight: 1000;
            margin: 12px 0;
            font-size: 22px;
            letter-spacing: 6px;
            text-align: left;
            text-shadow: 0.5px 0 0 currentColor;
        }

        /* 方块符号 */
        .square {
            font-size: 20px;
            line-height: 1;
            color: #EEEEEE;
        }
        
        /* 搜索框样式 */
        .search-box {
            width: 100%;
            height: 100%;
            display: none;
            align-items: center;
            padding: 0 10px;
            position: absolute;
            left: 0;
            top: 0;
            background-color: #000000;
        }
        
        .search-input {
            width: calc(100% - 50px);
            height: 40px;
            background: transparent;
            border: none;
            color: white;
            font-family: 'CustomFont', 'Microsoft YaHei', sans-serif;
            font-size: 16px;
            outline: none;
            padding: 0 10px;
        }
        
        .search-input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .search-cancel {
            color: white;
            margin-left: 10px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            width: 30px;
        }
        
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <!-- 顶部区域 -->
    <div class="header-container">
        <div class="black-box-wrapper">
            <div class="black-box" id="blackBox">
                <div class="black-box-view" id="searchView">
                    搜<br>索
                </div>
                <div class="view-divider" id="divider1"></div>
                <div class="black-box-view" id="moreView">
                    更<br>多
                </div>
                <div class="view-divider" id="divider2"></div>
                <div class="black-box-view" id="squareView">
                    联<br>系
                </div>
                
                <!-- 搜索框部分 -->
                <div class="search-box" id="searchBox">
                    <input type="text" class="search-input" id="searchInput" placeholder="搜索...">
                    <span class="search-cancel" id="searchCancel">×</span>
                </div>
            </div>
        </div>
        <div class="image-content">
            <div class="top-image">
                <img src="res/boke1.jpg" alt="文章图标">
            </div>
        </div>
    </div>
    
    <!-- 文章列表 -->
    <div class="content-list" id="contentList">
        <?php if (empty($txtFiles)): ?>
            <div class='list-item'><p>blog 目录中没有找到 .txt 文件</p></div>
        <?php else: ?>
            <?php foreach ($txtFiles as $file): ?>
                <?php
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $content = file_get_contents($file);
                
                // 优化内容处理：删除所有空白字符和空行
                $content = preg_replace('/\s+/', ' ', $content); // 替换所有空白字符为单个空格
                $content = trim($content); // 去除首尾空格
                
                // 截取前200个字符
                $excerpt = mb_substr($content, 0, 150, 'UTF-8');
                
                // 如果内容超过200字符，添加省略号
                if (mb_strlen($content, 'UTF-8') > 150) {
                    $excerpt .= '...';
                }
                ?>
                <div class="divider">——</div>
                <div class="list-item" data-title="<?= htmlspecialchars(strtolower($filename)) ?>" data-content="<?= htmlspecialchars(strtolower($excerpt)) ?>">
                    <h3 class="item-title"><?= htmlspecialchars($filename) ?></h3>
                    <p class="item-content"><?= nl2br(htmlspecialchars($excerpt)) ?></p>
                    <a href="article.php?file=<?= urlencode(basename($file)) ?>" 
                       class="read-more"
                       rel="prefetch"
                       data-prefetch="article.php?file=<?= urlencode(basename($file)) ?>">
                       阅读全文
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 性能优化脚本 -->
    <script>
    // 注册Service Worker
    if('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('ServiceWorker 注册成功'))
                .catch(err => console.log('注册失败: ', err));
        });
    }
    
    // 主动预加载文章内容
    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('[rel=prefetch]');
        links.forEach(link => {
            const url = link.getAttribute('data-prefetch');
            if(url) {
                // 使用fetch API预加载
                fetch(url, {credentials: 'include', mode: 'no-cors'})
                    .catch(() => {}); // 静默失败
                
                // 鼠标悬停时再次预加载确保最新
                link.addEventListener('mouseenter', () => {
                    fetch(url, {credentials: 'include', mode: 'no-cors'})
                        .catch(() => {});
                }, {once: true});
            }
        });
        
        // 搜索功能实现
        const searchView = document.getElementById('searchView');
        const moreView = document.getElementById('moreView');
        const squareView = document.getElementById('squareView');
        const divider1 = document.getElementById('divider1');
        const divider2 = document.getElementById('divider2');
        const searchBox = document.getElementById('searchBox');
        const searchInput = document.getElementById('searchInput');
        const searchCancel = document.getElementById('searchCancel');
        const contentList = document.getElementById('contentList');
        const listItems = document.querySelectorAll('.list-item');
        const dividers = document.querySelectorAll('.divider');
        
        // 点击搜索按钮
        searchView.addEventListener('click', function() {
            // 隐藏其他元素
            moreView.classList.add('hidden');
            squareView.classList.add('hidden');
            divider1.classList.add('hidden');
            divider2.classList.add('hidden');
            
            // 显示搜索框
            searchBox.style.display = 'flex';
            
            // 聚焦输入框
            searchInput.focus();
        });
        
        // 点击取消按钮
        searchCancel.addEventListener('click', function() {
            // 显示其他元素
            moreView.classList.remove('hidden');
            squareView.classList.remove('hidden');
            divider1.classList.remove('hidden');
            divider2.classList.remove('hidden');
            
            // 隐藏搜索框
            searchBox.style.display = 'none';
            
            // 清空输入框
            searchInput.value = '';
            
            // 显示所有文章
            listItems.forEach(item => item.style.display = '');
            dividers.forEach(div => div.style.display = '');
        });
        
        // 处理搜索输入
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            
            if (searchTerm === '') {
                // 显示所有文章
                listItems.forEach(item => item.style.display = '');
                dividers.forEach(div => div.style.display = '');
                return;
            }
            
            // 遍历所有文章
            listItems.forEach((item, index) => {
                const title = item.getAttribute('data-title');
                const content = item.getAttribute('data-content');
                
                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    item.style.display = '';
                    // 显示前面的分割线
                    if (dividers[index]) {
                        dividers[index].style.display = '';
                    }
                } else {
                    item.style.display = 'none';
                    // 隐藏前面的分割线
                    if (dividers[index]) {
                        dividers[index].style.display = 'none';
                    }
                }
            });
        });
    });
    </script>
</body>
</html>
