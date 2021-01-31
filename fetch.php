<?php

/**
 * 抓取WordPress文章转为hexo
 * Author iVampireSP.com
 * Blog iVampireSP.com
 */

# Hexo 目录
$hexo_path = "Hexo";

/* 站点列表 */
$site_list = json_decode(file_get_contents('sites.json'), true);

echo '本次要抓取' . count($site_list) . "个站点。\n";
foreach ($site_list as $site_list) {
    echo "正在抓取: $site_list ...\n";
    $json = json_decode(file_get_contents("https://$site_list/wp-json/wp/v2/posts?per_page=20&page=1"), true);
    foreach ($json as $array) {
        $title = str_replace("|", "-", $array['title']['rendered']);
        $date = str_replace("T", " ", $array['date']);
        $content = $array['content']['rendered'];
        $link = $array['link'];
        $filename = md5($site_list) . '-' . md5($title) . '.md';
        $write_content = <<<EOF
---
title: $title 由 $site_list
date: $date
tags: $site_list
---
$content

**该文章来自[$link]($link)**

**原作者同意后，MemoryArt将会拉取文章，但是请不要刻意的爬取本站。**
EOF;
        echo <<<EOF
-------------------------------------
             操作文件
    $site_list  -> $title
                ↓
$filename \n
EOF;
        file_put_contents("$hexo_path/source/_posts/$filename", $write_content);
    }
}

/* 全部完成后开始部署 */
echo "#####################################\n抓取写入完成，正在生成Hexo静态文件中... \n";
exec("cd $hexo_path && hexo g");
echo "全部完成！\n";
