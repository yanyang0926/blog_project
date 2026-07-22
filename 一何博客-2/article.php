<?php
// 设置 blog 目录路径
$blogDir = 'blog/';
$requestedFile = isset($_GET['file']) ? basename($_GET['file']) : '';
$filePath = $blogDir . $requestedFile;

// 检查文件有效性
$validFile = false;
if ($requestedFile && file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'txt') {
    $validFile = true;
    $content = file_get_contents($filePath);
    $filename = pathinfo($filePath, PATHINFO_FILENAME);
    
    // 处理图片链接转换（修改后的版本）
    $content = preg_replace_callback(
        '/【图片】(.*?)【图片】/',
        function($matches) {
            $url = trim($matches[1]);
            
            // 自动添加协议头（智能判断http/https）
            if (!preg_match('/^https?:\/\//i', $url)) {
                // 检查URL是否以"//"开头（协议相对URL）
                if (strpos($url, '//') === 0) {
                    $url = 'https:' . $url;
                } 
                // 检查是否是coolapk域名，默认使用https
                elseif (preg_match('/^(www\.)?coolapk\.com/i', $url) || 
                        preg_match('/^image\.coolapk\.com/i', $url)) {
                    $url = 'https://' . $url;
                }
                // 其他情况默认使用http
                else {
                    $url = 'http://' . $url;
                }
            }
            
            // 验证URL格式
            if(filter_var($url, FILTER_VALIDATE_URL)) {
                return '<div class="article-image"><img src="'.htmlspecialchars($url).'" alt="文章图片" onerror="this.style.display=\'none\'"></div>';
            }
            return $matches[0]; // 如果不是有效URL，保持原样
        },
        $content
    );
    
    // 处理代码块转换（保持不变）
    $content = preg_replace_callback(
        '/【代码】([\s\S]*?)【代码】/',
        function($matches) {
            $code = htmlspecialchars(trim($matches[1]));
            return '<div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">复制</button>
                <pre><code>'.$code.'</code></pre>
            </div>';
        },
        $content
    );
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $validFile ? htmlspecialchars($filename) : '文章不存在' ?> - 一何博客</title>
    <!-- 预加载关键资源 -->
    <link rel="preload" href="res/a.ttf" as="font" type="font/ttf" crossorigin>
    <style>
        /* 保持原有字体定义不变 */
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

        /* 保持原有基础样式不变 */
        body {
            font-family: 'CustomFont', 'Microsoft YaHei', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FFFFFF;
            -webkit-tap-highlight-color: transparent;
        }

        /* 保持原有顶部样式完全不变 */
        .header-container {
            position: relative;
            height: 60px;
            margin: 40px 0;
        }
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
        .black-box {
            width: 160px;
            height: 70px;
            background-color: #000000;
            margin-left: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 3px;
        }
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
            cursor: pointer;
        }
        .view-divider {
            width: 1px;
            height: 60%;
            background-color: rgba(255,255,255,0.3);
        }
        .image-content {
            position: absolute;
            left: 30px;
            display: flex;
            align-items: center;
            z-index: 2;
        }
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
        .divider {
            color: #000;
            font-weight: 1000;
            margin: 12px 0;
            font-size: 22px;
            letter-spacing: 6px;
            text-align: left;
            text-shadow: 0.5px 0 0 currentColor;
        }
        .square {
            font-size: 20px;
            line-height: 1;
            color: #EEEEEE;
        }

        /* 保持原有文章详情页样式不变 */
        .article-container {
            padding: 0 35px 0 38px;
            max-width: 800px;
            margin: 0 auto;
        }
        .article-title {
            font-size: 28px;
            font-weight: bold;
            margin: 30px 0 20px;
            color: #000000;
            word-break: break-word;
        }
        .article-content {
            font-size: 16px;
            line-height: 1.8;
            color: #333333;
            word-break: break-word;
            white-space: pre-wrap;
            margin-bottom: 30px;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #800020;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* 新增的图片样式 */
        .article-image {
            margin: 20px 0;
            text-align: center;
        }
        .article-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* 新增的代码块样式 */
        .code-block {
            position: relative;
            margin: 20px 0;
            background-color: #f5f5f5;
            border-radius: 4px;
            overflow-x: auto;
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        .code-block pre {
            margin: 0;
            padding: 15px;
            white-space: pre;
            overflow-x: auto;
        }
        .code-block code {
            display: block;
            color: #333;
        }
        
        /* 复制按钮样式 */
        .copy-btn {
            position: absolute;
            right: 8px;
            top: 8px;
            padding: 2px 8px;
            font-size: 12px;
            background-color: #e1e1e1;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
            font-family: 'CustomFont', 'Microsoft YaHei', sans-serif;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background-color: #d1d1d1;
        }
        .copy-btn:active {
            background-color: #c1c1c1;
        }
        
        /* 新增的提示框样式（不影响原有布局） */
        .toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            z-index: 1000;
            animation: fadeInOut 2s ease-in-out;
            display: none;
            font-size: 14px;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- 保持原有顶部区域完全不变 -->
    <div class="header-container">
        <div class="black-box-wrapper">
            <div class="black-box">
                <div class="black-box-view" id="shareView">
                    分<br>享
                </div>
                <div class="view-divider"></div>
                <div class="black-box-view">
                    更<br>多
                </div>
                <div class="view-divider"></div>
                <div class="black-box-view">
                    联<br>系
                </div>
            </div>
        </div>
        <div class="image-content">
            <div class="top-image">
                <img src="res/boke1.jpg" alt="文章图标">
            </div>
        </div>
    </div>

    <!-- 文章内容区域 -->
    <div class="article-container">
        <div class="divider">——</div>
        <?php if ($validFile): ?>
            <h1 class="article-title"><?= htmlspecialchars($filename) ?></h1>
            <div class="article-content"><?= $content ?></div>
        <?php else: ?>
            <h1 class="article-title">文章不存在</h1>
            <p class="article-content">您请求的文章不存在或已被删除。</p>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">← 返回文章列表</a>
    </div>

    <!-- 新增的提示框（不影响原有布局） -->
    <div class="toast" id="toast">链接已复制</div>

    <!-- 保持原有Service Worker注册不变 -->
    <script>
    if('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js');
        });
    }
    
    // 复制代码函数
    function copyCode(button) {
        const codeBlock = button.parentNode;
        const code = codeBlock.querySelector('code').textContent;
        const toast = document.getElementById('toast');
        
        // 方法1：尝试使用Clipboard API
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(
                () => {
                    toast.textContent = '代码已复制';
                    toast.style.display = 'block';
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 2000);
                },
                () => fallbackCopyCode(code) // 失败时使用备用方法
            );
            return;
        }
        
        // 方法2：使用execCommand的兼容方案
        fallbackCopyCode(code);
    }
    
    // 备用复制代码方法
    function fallbackCopyCode(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = 0;
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            const successful = document.execCommand('copy');
            const toast = document.getElementById('toast');
            if (successful) {
                toast.textContent = '代码已复制';
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2000);
            } else {
                alert('请手动选择并复制代码');
            }
        } catch (err) {
            alert('复制失败，请手动选择并复制代码');
        } finally {
            document.body.removeChild(textarea);
        }
    }
    
    // 优化的分享功能实现（不修改原有视图大小）
    document.addEventListener('DOMContentLoaded', function() {
        const shareView = document.getElementById('shareView');
        const toast = document.getElementById('toast');
        
        // 点击分享按钮（保持原有视图大小不变）
        shareView.addEventListener('click', function() {
            const currentUrl = window.location.href;
            
            // 优先尝试Web Share API（移动设备原生分享）
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: '分享这篇文章',
                    url: currentUrl
                }).catch(() => {
                    // 用户取消分享，尝试复制链接
                    copyToClipboard(currentUrl);
                });
                return;
            }
            
            // 普通复制功能
            copyToClipboard(currentUrl);
        });
        
        // 复制到剪贴板功能
        function copyToClipboard(text) {
            // 方法1：尝试使用Clipboard API
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(
                    () => {
                        toast.textContent = '链接已复制';
                        toast.style.display = 'block';
                        setTimeout(() => {
                            toast.style.display = 'none';
                        }, 2000);
                    },
                    () => fallbackCopy(text) // 失败时使用备用方法
                );
                return;
            }
            
            // 方法2：使用execCommand的兼容方案
            fallbackCopy(text);
        }
        
        // 备用复制方法（兼容旧浏览器）
        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = 0;
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                const successful = document.execCommand('copy');
                const toast = document.getElementById('toast');
                if (successful) {
                    toast.textContent = '链接已复制';
                    toast.style.display = 'block';
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 2000);
                } else {
                    alert('请手动复制链接: ' + text);
                }
            } catch (err) {
                alert('复制失败，请手动复制链接: ' + text);
            } finally {
                document.body.removeChild(textarea);
            }
        }
        
        // 图片加载错误处理
        document.querySelectorAll('.article-image img').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
            });
        });
    });
    </script>
</body>
</html>
