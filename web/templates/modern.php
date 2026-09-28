<?php

$headline = $newsletter['headline'] ?? '';
$summary = $newsletter['summary'] ?? '';
$body = $newsletter['body'] ?? '';
$edition = $newsletter['edition'] ?? '';
$publishDate = $newsletter['publish_date'] ?? '';
$location = $newsletter['location'] ?? '';

?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($headline) ?>
    </title>

    <style>

        body {
            margin: 0;
            padding: 30px;
            background: #f3f3f3;
            font-family:
                Arial,
                "Noto Sans Thai",
                sans-serif;
        }

        .preview-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;

    max-width: 1000px;
    margin: 0 auto 20px;

    padding: 12px 15px;

    background: #222;
    color: white;

    border-radius: 8px;
}

.preview-nav-brand {
    font-size: 16px;
    font-weight: 700;
}

.preview-nav-links {
    display: flex;
    gap: 8px;
}

.preview-nav-button {
    display: inline-block;

    padding: 8px 12px;

    background: white;
    color: #222;

    text-decoration: none;

    border-radius: 6px;

    font-size: 12px;
    font-weight: 600;
}

.preview-nav-button:hover {
    background: #eeeeee;
}

        .newsletter {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px;
        }

        .modern-header {
    margin-bottom: 35px;
    padding-bottom: 20px;
    border-bottom: 2px solid #222;
}

.brand {
    display: inline-block;
    margin-bottom: 25px;
    padding-bottom: 6px;
    border-bottom: 3px solid #222;

    font-size: 12px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
}

.headline {
    max-width: 800px;
    margin: 0;

    font-size: 52px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.summary {
    max-width: 720px;
    margin-top: 20px;

    font-size: 18px;
    line-height: 1.8;
    color: #555;
}

.meta {
    margin-top: 22px;

    font-size: 12px;
    line-height: 1.6;
    color: #777;
}

        .image-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
    margin-bottom: 35px;
}

.image-item {
    margin: 0;
    min-width: 0;
}

.image-item img {
    display: block;
    width: 100%;
    height: 280px;
    object-fit: cover;
}

.image-item:first-child {
    grid-column: 1 / -1;
}

.image-item:first-child img {
    height: 420px;
}

.image-caption {
    grid-column: 1 / -1;

    margin-top: 10px;
    padding-left: 10px;

    border-left: 2px solid #222;

    font-size: 12px;
    line-height: 1.6;
    color: #777;
}

.image-grid.images-1 {
    grid-template-columns: 1fr;
}

.image-grid.images-1 .image-item {
    grid-column: 1 / -1;
}

.image-grid.images-1 .image-item img {
    height: 420px;
}


.image-grid.images-2 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-2 .image-item {
    grid-column: auto;
}

.image-grid.images-2 .image-item img {
    height: 320px;
}


.image-grid.images-3 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-3 .image-item:first-child {
    grid-column: 1 / -1;
}

.image-grid.images-3 .image-item img {
    height: 280px;
}

.image-grid.images-3 .image-item:first-child img {
    height: 420px;
}


.image-grid.images-4 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-4 .image-item:first-child {
    grid-column: 1 / -1;
}

.image-grid.images-4 .image-item:last-of-type {
    grid-column: 1 / -1;
}

.image-grid.images-4 .image-item img {
    height: 280px;
}

.image-grid.images-4 .image-item:first-child img,
.image-grid.images-4 .image-item:last-of-type img {
    height: 320px;
}

        .article {
    max-width: 760px;
    margin: 0 auto;

    font-size: 17px;
    line-height: 2;

    color: #222;
}

.article p {
    margin-top: 0;
    margin-bottom: 24px;
}

        .newsletter-footer {
    margin-top: 45px;
    padding-top: 15px;

    border-top: 1px solid #222;

    font-size: 11px;
    line-height: 1.7;
    color: #888;
}

.newsletter-footer strong {
    color: #222;
    letter-spacing: 1px;
}

@media (max-width: 700px) {

    body {
        padding: 10px;
    }

    .preview-nav {
    flex-direction: column;
    gap: 10px;

    align-items: stretch;
}

.preview-nav-brand {
    text-align: center;
}

.preview-nav-links {
    width: 100%;
}

.preview-nav-button {
    flex: 1;
    text-align: center;
}

    .newsletter {
        padding: 30px 20px;
    }

    .headline {
        font-size: 36px;
        line-height: 1.3;
    }

    .summary {
        font-size: 16px;
        line-height: 1.7;
    }

    .modern-header {
        margin-bottom: 25px;
    }


    /* Image layout */

    .image-grid {
        gap: 4px;
        margin-bottom: 25px;
    }

    .image-grid.images-1 {
        grid-template-columns: 1fr;
    }

    .image-grid.images-1 .image-item {
        grid-column: 1 / -1;
    }

    .image-grid.images-1 .image-item img {
        height: 260px;
    }


    .image-grid.images-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .image-grid.images-2 .image-item {
        grid-column: auto;
    }

    .image-grid.images-2 .image-item img {
        height: 180px;
    }


    .image-grid.images-3 {
        grid-template-columns: repeat(2, 1fr);
    }

    .image-grid.images-3 .image-item:first-child {
        grid-column: 1 / -1;
    }

    .image-grid.images-3 .image-item img {
        height: 150px;
    }

    .image-grid.images-3 .image-item:first-child img {
        height: 240px;
    }


    .image-grid.images-4 {
        grid-template-columns: repeat(2, 1fr);
    }

    .image-grid.images-4 .image-item:first-child {
        grid-column: 1 / -1;
    }

    .image-grid.images-4 .image-item:last-of-type {
        grid-column: 1 / -1;
    }

    .image-grid.images-4 .image-item img {
        height: 150px;
    }

    .image-grid.images-4 .image-item:first-child img,
    .image-grid.images-4 .image-item:last-of-type img {
        height: 220px;
    }


    /* Article */

    .article {
        font-size: 16px;
        line-height: 1.9;
    }

    .article p {
        margin-bottom: 20px;
    }

}

@media print {

.preview-nav {
    display: none;
}

    @page {
        size: A4;
        margin: 10mm;
    }

    body {
        padding: 0;
        margin: 0;
        background: white;
    }

    .newsletter {
        width: 100%;
        max-width: none;
        padding: 0;
        margin: 0;
    }

    .modern-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
    }

    .brand {
        margin-bottom: 12px;
    }

    .headline {
        font-size: 38px;
    }

    .summary {
        margin-top: 12px;
        font-size: 15px;
        line-height: 1.6;
    }

    .meta {
        margin-top: 12px;
    }


    /* Images */

    .image-grid {
        gap: 4px;
        margin-bottom: 20px;
    }

    .image-grid.images-1 .image-item img {
        height: 260px;
    }

    .image-grid.images-2 .image-item img {
        height: 190px;
    }

    .image-grid.images-3 .image-item img {
        height: 170px;
    }

    .image-grid.images-3 .image-item:first-child img {
        height: 260px;
    }

    .image-grid.images-4 .image-item img {
        height: 160px;
    }

    .image-grid.images-4 .image-item:first-child img,
    .image-grid.images-4 .image-item:last-of-type img {
        height: 220px;
    }


    /* Article */

    .article {
        max-width: none;
        font-size: 14px;
        line-height: 1.7;
    }

    .article p {
        margin-bottom: 12px;
    }


    /* Footer */

    .newsletter-footer {
        margin-top: 20px;
        padding-top: 8px;
    }

}

    </style>

</head>

<body>

<?php require __DIR__ . '/../inc/preview-nav.php'; ?>

    <main class="newsletter">

        <header class="modern-header">

            <div class="brand">
                TNAI • Thai Newsletter AI
            </div>

            <h1 class="headline">
                <?= htmlspecialchars($headline) ?>
            </h1>

            <?php if (!empty($summary)): ?>

                <div class="summary">
                    <?= nl2br(
                        htmlspecialchars($summary)
                    ) ?>
                </div>

            <?php endif; ?>

            <div class="meta">

                <?php if (!empty($edition)): ?>

                    ฉบับ <?= htmlspecialchars($edition) ?>

                <?php endif; ?>

                <?php if (!empty($publishDate)): ?>

                    · <?= htmlspecialchars($publishDate) ?>

                <?php endif; ?>

                <?php if (!empty($location)): ?>

                    · <?= htmlspecialchars($location) ?>

                <?php endif; ?>

            </div>

        </header>


        <?php if (!empty($images)): ?>

            <section class="image-grid images-<?= min(count($images), 4) ?>">

                <?php foreach ($images as $image): ?>

                    <figure class="image-item">

                        <img
                            src="<?= htmlspecialchars($basePath) ?>/storage/images/<?= (int) $newsletter['id'] ?>/<?= htmlspecialchars(
                                $image['filename']
                            ) ?>"
                            alt=""
                        >

                    </figure>

                <?php endforeach; ?>


                <?php if (!empty($newsletter['image_caption'])): ?>

                    <div class="image-caption">

                        <?= nl2br(
                            htmlspecialchars(
                                $newsletter['image_caption']
                            )
                        ) ?>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>


        <article class="article">

            <?= nl2br(
                htmlspecialchars($body)
            ) ?>

        </article>


        <footer class="newsletter-footer">

            <strong>TNAI</strong>
            — Thai Newsletter AI

            <br>

            สร้างโดยระบบ AI สำหรับการจัดทำจดหมายข่าว

        </footer>

    </main>

</body>

</html>