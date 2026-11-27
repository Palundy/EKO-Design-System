<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use EKO\DesignSystem\DesignSystem;
use EKO\DesignSystem\Button;
use EKO\DesignSystem\SectionHeading;
use EKO\DesignSystem\SpecTable;

use EKO\DesignSystem\Fields\Input;
use EKO\DesignSystem\Fields\Textarea;
use EKO\DesignSystem\Fields\Dropdown;

use EKO\DesignSystem\Cards\ProductCard;
use EKO\DesignSystem\Cards\ContactCard;
use EKO\DesignSystem\Cards\ApplicationCard;
use EKO\DesignSystem\Cards\IndustryCard;
use EKO\DesignSystem\Cards\ArticleCard;

use EKO\DesignSystem\Navigation\Breadcrumb;
use EKO\DesignSystem\Navigation\NavItem;


// Use addons
use EKO\DesignSystem\Addons\EkoData\EkoData;
use EKO\DesignSystem\Addons\EkoData\StatusTracker;

use EKO\DesignSystem\Addons\Icf02\Icf02;
use EKO\DesignSystem\Addons\Icf02\Sidebar;
use EKO\DesignSystem\Addons\Icf02\Pincode;


// Use EKO Data addon components in the page
DesignSystem::enableAddon(EkoData::class);
DesignSystem::enableAddon(Icf02::class);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?= DesignSystem::stylesheet() ?>
    <?= DesignSystem::scripts() ?>
</head>
<body>
    <div style="margin-bottom: 24px;">
        <?php echo new Pincode(
            title: "Ready to calibrate?",
            // logo: "/assets/images/eko-logo.svg",
            // logoAlt: "EKO Instruments",
            length: 5,
            message: "Seems like you need to enter your pincode...",
            state: "locked",
            errorMessage: "Almost right...",
            unlockedMessage: "Nicely done!",
            submitLabel: "Enter",
            deleteLabel: "Back",
        );?>
    </div>
</body>
<script>
    document.addEventListener("icf02-pincode-submit", async (event) => {
        const pincode = event.detail.pincode;
        if (pincode === "12345") {
            event.detail.unlock();
            setTimeout(() => {
                // Go to showcase.php
                window.location.href = "showcase.php";
            }, 1000); // Redirect after 1 second
        } else {
            event.detail.reject();
        }
    });
</script>
</html>