<?php
require_once __DIR__ . '/vendor/autoload.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="assets/js/dropdowns.js" defer></script>
</head>
<body>
    <table>
        <tr><th colspan="7">Buttons</th></tr>
        <tr>
            <th></th>
            <th>Default</th>
            <th>Disabled</th>
            <th>Loading</th>
            <th>Negative</th>
        </tr>
        <tr>
            <td>Primary</td>
            <td><?  echo new Button("Label"); ?></td>
            <td><?  echo new Button("Label", state: "disabled"); ?></td>
            <td><?  echo new Button("Label", state: "loading"); ?></td>
            <td><?  echo new Button("Label", state: "negative"); ?></td>
        </tr>
        <tr>
            <td>Destructive</td>
            <td><?  echo new Button("Label", style: "destructive"); ?></td>
            <td><?  echo new Button("Label", style: "destructive", state: "disabled"); ?></td>
            <td><?  echo new Button("Label", style: "destructive", state: "loading"); ?></td>
            <td><?  echo new Button("Label", style: "destructive", state: "negative"); ?></td>
        </tr>
        <tr>
            <td>Positive</td>
            <td><?  echo new Button("Label", style: "positive"); ?></td>
            <td><?  echo new Button("Label", style: "positive", state: "disabled"); ?></td>
            <td><?  echo new Button("Label", style: "positive", state: "loading"); ?></td>
            <td><?  echo new Button("Label", style: "positive", state: "negative"); ?></td>
        </tr>
        <tr>
            <td>Enquiry</td>
            <td><?  echo new Button("Label", style: "enquiry"); ?></td>
            <td><?  echo new Button("Label", style: "enquiry", state: "disabled"); ?></td>
            <td><?  echo new Button("Label", style: "enquiry", state: "loading"); ?></td>
            <td><?  echo new Button("Label", style: "enquiry", state: "negative"); ?></td>
        </tr>
    </table>

    <div style="display:flex;gap:20px;margin-bottom: 24px;">
        <? echo new Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true
        ); ?>

        <? echo new Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "disabled"
        ); ?>

        <? echo new Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "error"
        ); ?>

        <? echo new Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "success"
        ); ?>
    </div>
    
    <div style="display:flex;gap:20px;margin-bottom: 24px;">
        <? echo new Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true
        ); ?>

        <? echo new Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "disabled"
        ); ?>

        <? echo new Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "error"
        ); ?>

        <? echo new Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "success"
        ); ?>
    </div>
    
    <div style="display:flex;gap:20px;margin-bottom: 24px;">
        <?php $options = [
            "ms-80sh" => "MS-80SH",
            "ms-80" => "MS-80",
            "ms-60" => "MS-60",
            "ms-40" => "MS-40"
        ]; ?>
        <? echo new Dropdown(
            label: "Product of interest",
            info: "Please select a product.",
            id: "product",
            options: $options,
            required: true
        ); ?>

        <? echo new Dropdown(
            label: "Product of interest",
            info: "This field is disabled.",
            id: "product-disabled",
            options: $options,
            required: true,
            state: "disabled"
        ); ?>

        <? echo new Dropdown(
            label: "Product of interest",
            info: "Please select a product.",
            id: "product-error",
            options: $options,
            required: true,
            state: "error"
        ); ?>

        <? echo new Dropdown(
            label: "Product of interest",
            info: "Successfully selected!",
            value: "ms-80sh",
            id: "product-success",
            options: $options,
            required: true,
            state: "success"
        ); ?>
    </div>

    <div style="margin-bottom: 24px;display: flex; gap: 24px;">
        <? echo new ProductCard(
            title: "MS-80SH",
            label: "Class A",
            description: "Class A pyranometer with ventilator and heater for utility-scale PV",
            image: "assets/images/ms80sh.png",
            imageAlt: "MS-80SH Pyranometer",
            table: [
                "ISO 9060:2018" => "spectrally flat, Class A",
                "IEC 61724-1:2021" => "Class A system compliant",
                "Warranty" => "5 years",
                "Recalibration" => "5 years",
            ],
            cta: "Call to action"
        ); ?>

        <? echo new ProductCard(
            title: "ICF-02",
            label: "New!",
            description: "From ISO standards and uncertainty budgets to fully automated indoor calibration, everything your lab needs to know, in one place.",
            image: "assets/images/icf-02.png",
            imageAlt: "EKO Instruments ICF-02 - Indoor Calibration Facility",
            table: [
                ["Standard", "IS0 9847:2023"],
                ["Light Source", "300 LED or 150W Metal Halide"]
            ],
            cta: "Request Quote"
        ); ?>

        <? echo new ProductCard(
            title: "Product Card",
            description: "This is a card without an image, without a table and without a label.",
            cta: "Call to action"
        ); ?>
    </div>

    <div style="margin-bottom: 24px;display: flex; gap: 24px;">
        <? echo new ContactCard(
            first: "Palau van Helden",
            second: "EKO Instruments B.V.",
            third: "Project Development Engineer",
            email: "palau@vhelden.nl",
            phone: "+31 (0)6 24175606",
            website: "vhelden.nl",
            button: "Ask Palau",
            buttonIcon: "typing-outline",
            buttonAttributes: [
                "onclick" => "alert('eventListeners also work here');"
            ]
        ); ?>

        <? echo new ContactCard(
            first: "John Doe",
            second: "Example Corp.",
            third: "Sales Manager",
            email: "john.doe@example.com",
            phone: "+31 (0)6 12345678",
            website: "example.com",
            button: "Contact John",
            buttonIcon: "envelope-outline",
            image: "assets/images/user-silhouette-" . str_pad(rand(1, 5), 2, "0", STR_PAD_LEFT) . ".svg"
        ); ?>

        <? echo new ContactCard(
            first: "Contact Card",
            third: "This contact card has minimal information.",
            email: "contact@example.com",
        ); ?>
    </div>

    <div style="display: flex; flex-direction: column; gap: 48px; width: 1024px; margin: 48px 0;">
        <? echo new SpecTable(
            title: "Specifications",
            rows: [
                "Wavelength Range (nm)" => "285 to 3000",
                "Maximum Operational Irradiance (W/m²)" => "4000",
                "Signal Output" => "Modbus 485 RTU / SDI-12",
                "EMC / Surge Protection Standard" => "IEC61326-1 (Industrial Electromagnetic Environment test level) / IEC61000-4-5 (Level 2, Test Criterion B)",
                "Ingress Protection" => "IP 67",
            ]
        ); ?>

        <? echo new SpecTable(
            title: "ISO Specifications",
            header: ["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"],
            rows: [
                "Response time 95%" => ["< 10s", "< 0.5s"],
                "Zero offset A, Thermal Radiation (200W/m²)" => ["± 7W/m²", "± 1W/m²"],
                "Non-stability (change/year)" => ["± 0.8%", "< 0.5% / 5 years"],
                "Spectral Error" => ["± 0.5%", "± 0.2%"],
            ]
        ); ?>
    </div>
    <div style="display: flex; flex-direction: column; gap: 32px; margin: 48px 0;">
        <? echo new Breadcrumb([
            "Home" => "/",
            "Pyranometers" => "/pyranometers",
            "MS-80SH" => "/products/ms-80sh",
            "Talk to an Expert",
        ]); ?>

        <div style="display: flex; gap: 32px;">
            <? echo new NavItem("Products", "/products"); ?>
            <? echo new NavItem("Industries", "/industries", active: true); ?>
            <? echo new NavItem("About EKO", "/about"); ?>
        </div>

        <? echo new SectionHeading("Popular Products", link: "View all", linkUrl: "/products"); ?>

        <div style="display: flex; gap: 24px; align-items: flex-start;">
            <? echo new ApplicationCard(
                title: "Solar Energy",
                description: "Resource assessment, performance monitoring and plant optimization.",
                cta: "Learn More",
                ctaUrl: "/applications/solar-energy"
            ); ?>

            <? echo new ArticleCard(
                title: "Headline placeholder for new about everything, maximum three lines here.",
                url: "https://www.eko-instruments.com/blog/example",
                tag: "Instruments",
                description: "Here is the product description or highlight. It must contain maximum three lines of text. It must contain maximum three lines of text.",
                readTime: "6 minutes read",
                share: true
            ); ?>
        </div>

        <? echo new IndustryCard(
            title: "Solar Energy",
            description: "Precision instruments for solar resource assessment, PV performance monitoring, and weather station integration",
            url: "/industries/solar-energy"
        ); ?>
    </div>
</body>
</html>