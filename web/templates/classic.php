<?php
/*
|--------------------------------------------------------------------------
| Classic Newsletter Template
|--------------------------------------------------------------------------
|
| Variables provided by preview.php:
|
| $newsletter
| $images
|
*/
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
        <?= htmlspecialchars(
            $newsletter['headline']
        ) ?>
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 30px 15px;

            background: #e9e9e9;

            color: #1f1f1f;

            font-family:
                Georgia,
                "Noto Serif Thai",
                serif;

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

            width: 100%;

            max-width: 900px;

            margin: 0 auto;

            background: #ffffff;

            border: 1px solid #cccccc;

            padding: 45px 50px;

        }


        /* ---------------------------------------------------------
           Header
        --------------------------------------------------------- */

        .newsletter-header {

    text-align: center;

    border-bottom: 4px double #222;

    padding-bottom: 20px;

    margin-bottom: 30px;

}


/* Small publication label */

.brand {

    font-family:
        Arial,
        "Noto Sans Thai",
        sans-serif;

    font-size: 14px;

    font-weight: bold;

    letter-spacing: 4px;

    margin-bottom: 10px;

    text-transform: uppercase;

}


/* Main newsletter name */

.newsletter-title {

    margin: 0;

    font-size: 44px;

    letter-spacing: 1px;

    line-height: 1.2;

}


/* Edition / date / location */

.header-meta {

    display: flex;

    justify-content: center;

    align-items: center;

    flex-wrap: wrap;

    gap: 4px;

    margin-top: 15px;

    font-family:
        Arial,
        "Noto Sans Thai",
        sans-serif;

    font-size: 13px;

    color: #555;

}


.header-meta span {

    margin: 0 4px;

}


        /* ---------------------------------------------------------
           Headline
        --------------------------------------------------------- */

        .headline {

    margin: 0 auto 18px;

    max-width: 780px;

    font-size: 38px;

    line-height: 1.35;

    text-align: center;

    font-weight: 700;

}


        .headline-rule {

    width: 90px;

    height: 3px;

    background: #222;

    margin: 0 auto 25px;

}


        /* ---------------------------------------------------------
           Summary
        --------------------------------------------------------- */

        .summary {

    max-width: 760px;

    margin: 0 auto 30px;

    padding: 0 20px;

    text-align: center;

    font-size: 18px;

    line-height: 1.8;

    font-weight: 600;

    color: #444;

}

        /* ---------------------------------------------------------
   Images
--------------------------------------------------------- */

.image-grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 15px;

    margin-bottom: 30px;

}


/*
|--------------------------------------------------------------------------
| Image item
|--------------------------------------------------------------------------
*/

.image-item {

    margin: 0;

    min-width: 0;

}


/*
|--------------------------------------------------------------------------
| Images
|--------------------------------------------------------------------------
*/

.image-item img {

    display: block;

    width: 100%;

    height: 260px;

    object-fit: cover;

    border: 1px solid #ccc;

}


/*
|--------------------------------------------------------------------------
| First image
|--------------------------------------------------------------------------
|
| The first image acts as the main / hero image.
|
*/

.image-item:first-child {

    grid-column: 1 / -1;

}


.image-item:first-child img {

    height: 400px;

}


/*
|--------------------------------------------------------------------------
| Two-image layout
|--------------------------------------------------------------------------
|
| The second image becomes a half-width image.
|
*/

.image-grid.images-2 .image-item {

    grid-column: 1 / -1;

}


/*
|--------------------------------------------------------------------------
| Three-image layout
|--------------------------------------------------------------------------
|
| Image 1 = large hero
| Image 2 + 3 = bottom row
|
*/

.image-grid.images-3 .image-item {

    grid-column: auto;

}


.image-grid.images-3 .image-item:first-child {

    grid-column: 1 / -1;

}


/*
|--------------------------------------------------------------------------
| Caption
|--------------------------------------------------------------------------
*/

.image-caption {

    grid-column: 1 / -1;

    margin-top: -5px;

    margin-bottom: 5px;

    padding-left: 10px;

    border-left: 3px solid #222;

    font-family:
        Arial,
        "Noto Sans Thai",
        sans-serif;

    font-size: 12px;

    line-height: 1.6;

    color: #666;

}


        /* ---------------------------------------------------------
           Article
        --------------------------------------------------------- */

        .article {

    max-width: 780px;

    margin: 0 auto;

    font-size: 17px;

    line-height: 2;

    text-align: justify;

    color: #252525;

}


.article p {

    margin-top: 0;

    margin-bottom: 20px;

}


        /* ---------------------------------------------------------
           Footer
        --------------------------------------------------------- */

        .newsletter-footer {

    margin-top: 45px;

    padding-top: 15px;

    border-top: 4px double #222;

    text-align: center;

    font-family:
        Arial,
        "Noto Sans Thai",
        sans-serif;

    font-size: 11px;

    line-height: 1.7;

    color: #777;

}


.newsletter-footer strong {

    color: #222;

    letter-spacing: 1px;

}


        /* ---------------------------------------------------------
           Responsive
        --------------------------------------------------------- */

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

                padding: 25px 20px;

            }


            .newsletter-title {

                font-size: 32px;

            }


            .headline {

    font-size: 29px;

    line-height: 1.4;

}


            .summary {

    padding: 0;

    font-size: 16px;

    line-height: 1.7;

}


            .image-grid {

    grid-template-columns: repeat(2, 1fr);

    gap: 10px;

}


.image-item:first-child {

    grid-column: 1 / -1;

}


.image-item img {

    height: 180px;

}


.image-item:first-child img {

    height: 260px;

}

.image-grid.images-1 {

    grid-template-columns: 1fr;

}


.image-grid.images-1 .image-item {

    grid-column: 1 / -1;

}


.image-grid.images-2 {

    grid-template-columns: 1fr;

}


.image-grid.images-2 .image-item {

    grid-column: 1 / -1;

}


.image-grid.images-3 {

    grid-template-columns: repeat(2, 1fr);

}


.image-grid.images-3 .image-item:first-child {

    grid-column: 1 / -1;

}


.image-grid.images-3 .image-item img {

    height: 180px;

}


.image-grid.images-3 .image-item:first-child img {

    height: 260px;

}

            .article {

                font-size: 16px;

                text-align: left;

            }

        }


        /* ---------------------------------------------------------
           Print
        --------------------------------------------------------- */

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

        border: none;

        padding: 0;

        margin: 0;

    }

.image-item img {
    height: 180px;
}

.image-item:first-child img {
    height: 260px;
}

    .image-grid {

        break-inside: avoid;

    }

    .image-caption {

        break-inside: avoid;

    }


    .headline {

        break-after: avoid;

    }


    .article {

        max-width: none;

    }

    .newsletter-header {
    margin-bottom: 15px;
    padding-bottom: 10px;
}

.headline {
    margin-bottom: 10px;
}

.headline-rule {
    margin-bottom: 12px;
}

.summary {
    margin-bottom: 15px;
}

.image-grid {
    margin-bottom: 15px;
}

.article p {
    margin-bottom: 10px;
}

.newsletter-footer {
    margin-top: 15px;
    padding-top: 8px;
}

}

    </style>

</head>


<body>

<?php require __DIR__ . '/../inc/preview-nav.php'; ?>


<main class="newsletter">


    <!-- Header -->

    <header class="newsletter-header">


        <div class="brand">

    TNAI • Thai Newsletter AI

</div>


        <h1 class="newsletter-title">

            Thai Newsletter

        </h1>


        <div class="header-meta">


            <?php if (!empty($newsletter['edition'])): ?>

                <span>

                    ฉบับที่
                    <?= htmlspecialchars(
                        $newsletter['edition']
                    ) ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($newsletter['publish_date'])): ?>

                <span>•</span>

                <span>

                    <?= htmlspecialchars(
                        $newsletter['publish_date']
                    ) ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($newsletter['location'])): ?>

                <span>•</span>

                <span>

                    <?= htmlspecialchars(
                        $newsletter['location']
                    ) ?>

                </span>

            <?php endif; ?>


        </div>


    </header>


    <!-- Headline -->

    <h2 class="headline">

        <?= htmlspecialchars(
            $newsletter['headline']
        ) ?>

    </h2>


    <div class="headline-rule"></div>


    <!-- Summary -->

    <?php if (!empty($newsletter['summary'])): ?>

        <div class="summary">

            <?= nl2br(
                htmlspecialchars(
                    $newsletter['summary']
                )
            ) ?>

        </div>

    <?php endif; ?>


    <!-- Images -->

    <?php if (!empty($images)): ?>

        <section
    class="image-grid images-<?= min(count($images), 3) ?>"
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


    <!-- Article -->

    <article class="article">

        <?= nl2br(
            htmlspecialchars(
                $newsletter['body']
            )
        ) ?>

    </article>


    <!-- Footer -->

    <footer class="newsletter-footer">

    <strong>TNAI</strong>
    — Thai Newsletter AI

    <br>

    สร้างโดยระบบ AI สำหรับการจัดทำจดหมายข่าว

</footer>

</main>


</body>

</html>