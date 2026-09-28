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
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px;
        }


        /* Header */

        .focus-header {
            margin-bottom: 35px;
        }

        .brand {
            margin-bottom: 20px;

            font-size: 12px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        .headline {
            margin: 0;

            font-size: 56px;
            line-height: 1.15;
            font-weight: 800;
        }

        .summary {
            max-width: 750px;
            margin-top: 20px;

            font-size: 18px;
            line-height: 1.8;
            color: #555;
        }

        .meta {
            margin-top: 20px;

            font-size: 12px;
            color: #777;
        }


        /* Image gallery */

        .image-grid {
    margin-bottom: 40px;
}

.image-item {
    margin: 0;
}

.image-item img {
    display: block;
    width: 100%;
    height: 320px;
    object-fit: cover;
}

/* Hero image */

.image-grid.images-1 .image-item:first-child img,
.image-grid.images-2 .image-item:first-child img,
.image-grid.images-3 .image-item:first-child img,
.image-grid.images-4 .image-item:first-child img,
.image-grid.images-5 .image-item:first-child img,
.image-grid.images-6 .image-item:first-child img {
    height: 500px;
    object-fit: cover;
}

/* Image layouts */

.image-grid {
    display: grid;
    gap: 8px;
}


/* 1 image */

.image-grid.images-1 {
    grid-template-columns: 1fr;
}


/* 2 images */

.image-grid.images-2 {
    grid-template-columns: repeat(2, 1fr);
    align-items: stretch;
}

.image-grid.images-2 .image-item {
    margin: 0;
    height: 320px;
    overflow: hidden;
}

.image-grid.images-2 .image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


/* 3 images */

.image-grid.images-3 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-3 .image-item:first-child {
    grid-column: 1 / -1;
}


/* 4 images */

.image-grid.images-4 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-4 .image-item:first-child {
    grid-column: 1 / -1;
}

.image-grid.images-4 .image-item:nth-child(4) {
    grid-column: 1 / -1;
}

/* 5 images */

.image-grid.images-5 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-5 .image-item:nth-of-type(1) {
    grid-column: 1 / -1;
}

.image-grid.images-5 .image-item:nth-of-type(2) {
    grid-column: 1;
}

.image-grid.images-5 .image-item:nth-of-type(3) {
    grid-column: 2;
}

.image-grid.images-5 .image-item:nth-of-type(4) {
    grid-column: 1;
}

.image-grid.images-5 .image-item:nth-of-type(5) {
    grid-column: 2;
}


/* 6 images */

.image-grid.images-6 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-6 .image-item:first-child {
    grid-column: 1 / -1;
}

.image-grid.images-6 .image-item:last-of-type {
    grid-column: 1 / -1;
}

        .image-caption {
            margin-top: 10px;

            font-size: 12px;
            line-height: 1.6;
            color: #777;
        }


        /* Article */

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


        /* Footer */

        .newsletter-footer {
            margin-top: 50px;
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


    /* Header */

    .headline {
        font-size: 38px;
        line-height: 1.3;
    }

    .summary {
        font-size: 16px;
        line-height: 1.7;
    }

    .focus-header {
        margin-bottom: 25px;
    }


    /* Image gallery */

    .image-grid {
        gap: 4px;
        margin-bottom: 25px;
    }

    .image-item img {
        height: 180px;
    }


    /* Hero image */

    .image-grid.images-1 .image-item:first-child img,
    .image-grid.images-2 .image-item:first-child img,
    .image-grid.images-3 .image-item:first-child img,
    .image-grid.images-4 .image-item:first-child img,
    .image-grid.images-5 .image-item:first-child img,
    .image-grid.images-6 .image-item:first-child img {
        height: 280px;
    }

    /* 2 images */

.image-grid.images-2 {
    grid-template-columns: repeat(2, 1fr);
}

.image-grid.images-2 .image-item {
    height: 180px;
}

.image-grid.images-2 .image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

    /* Article */

    .article {
        font-size: 16px;
        line-height: 1.9;
    }

    .article p {
        margin-bottom: 20px;
    }


    /* Footer */

    .newsletter-footer {
        margin-top: 35px;
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


    /* Header */

    .focus-header {
        margin-bottom: 15px;
    }

    .brand {
        margin-bottom: 10px;
        font-size: 10px;
    }

    .headline {
        font-size: 34px;
        line-height: 1.15;
    }

    .summary {
        margin-top: 10px;
        font-size: 13px;
        line-height: 1.5;
    }

    .meta {
        margin-top: 10px;
        font-size: 10px;
    }


    /* Image gallery */

.image-grid {
    gap: 3px;
    margin-bottom: 15px;
}

.image-item {
    break-inside: avoid;
    page-break-inside: avoid;
}

.image-item img {
    height: 180px;
    object-fit: cover;
}


/* Hero image */

.image-grid.images-1 .image-item:first-child img {
    height: 430px;
}


/* 2 images */

.image-grid.images-2 .image-item {
    height: 220px;
}

.image-grid.images-2 .image-item img {
    height: 100%;
}


/* 3 images */

.image-grid.images-3 .image-item:first-child img {
    height: 300px;
}

.image-grid.images-3 .image-item:not(:first-child) img {
    height: 170px;
}


/* 4 images */

.image-grid.images-4 .image-item:first-child img {
    height: 280px;
}

.image-grid.images-4 .image-item:not(:first-child) img {
    height: 160px;
}


/* 5 images */

.image-grid.images-5 .image-item:first-child img {
    height: 280px;
}

.image-grid.images-5 .image-item:not(:first-child) img {
    height: 160px;
}


/* 6 images */

.image-grid.images-6 .image-item:first-child img {
    height: 240px;
}

.image-grid.images-6 .image-item:not(:first-child) img {
    height: 125px;
}

    /* Caption */

    .image-caption {
        margin-top: 5px;
        font-size: 9px;
        line-height: 1.4;
    }


    /* Article */

    .article {
        max-width: none;
        font-size: 12px;
        line-height: 1.6;
    }

    .article p {
        margin-bottom: 8px;
    }


    /* Footer */

    .newsletter-footer {
        margin-top: 12px;
        padding-top: 6px;
        font-size: 9px;
        line-height: 1.4;
    }

}
    </style>

</head>

<body>

<?php require __DIR__ . '/../inc/preview-nav.php'; ?>

    <main class="newsletter">


        <header class="focus-header">

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

            <section
    class="image-grid images-<?= count($images) ?>"
>

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