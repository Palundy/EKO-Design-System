<?php
require 'php/button.php';
require 'php/fields.php';
require 'php/cards.php';
require 'php/spec-table.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/main.css">
    <script src="js/dropdowns.js" defer></script>
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
            <td><?  new Button("Label"); ?></td>
            <td><?  new Button("Label", state: "disabled"); ?></td>
            <td><?  new Button("Label", state: "loading"); ?></td>
            <td><?  new Button("Label", state: "negative"); ?></td>
        </tr>
        <tr>
            <td>Destructive</td>
            <td><?  new Button("Label", style: "destructive"); ?></td>
            <td><?  new Button("Label", style: "destructive", state: "disabled"); ?></td>
            <td><?  new Button("Label", style: "destructive", state: "loading"); ?></td>
            <td><?  new Button("Label", style: "destructive", state: "negative"); ?></td>
        </tr>
        <tr>
            <td>Positive</td>
            <td><?  new Button("Label", style: "positive"); ?></td>
            <td><?  new Button("Label", style: "positive", state: "disabled"); ?></td>
            <td><?  new Button("Label", style: "positive", state: "loading"); ?></td>
            <td><?  new Button("Label", style: "positive", state: "negative"); ?></td>
        </tr>
        <tr>
            <td>Enquiry</td>
            <td><?  new Button("Label", style: "enquiry"); ?></td>
            <td><?  new Button("Label", style: "enquiry", state: "disabled"); ?></td>
            <td><?  new Button("Label", style: "enquiry", state: "loading"); ?></td>
            <td><?  new Button("Label", style: "enquiry", state: "negative"); ?></td>
        </tr>
    </table>

    <div style="display:flex;gap:20px;margin-bottom: 24px;">
        <? new Fields\Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true
        ); ?>

        <? new Fields\Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "disabled"
        ); ?>

        <? new Fields\Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "error"
        ); ?>

        <? new Fields\Input(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "success"
        ); ?>
    </div>
    
    <div style="display:flex;gap:20px;margin-bottom: 24px;">
        <? new Fields\Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true
        ); ?>

        <? new Fields\Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "disabled"
        ); ?>

        <? new Fields\Textarea(
            label: "Full Name",
            info: "Your complete name.",
            id: "full-name",
            placeholder: "John Doe", 
            required: true,
            state: "error"
        ); ?>

        <? new Fields\Textarea(
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
        <? new Fields\Dropdown(
            label: "Product of interest",
            info: "Please select a product.",
            id: "product",
            options: $options,
            required: true
        ); ?>

        <? new Fields\Dropdown(
            label: "Product of interest",
            info: "This field is disabled.",
            id: "product-disabled",
            options: $options,
            required: true,
            state: "disabled"
        ); ?>

        <? new Fields\Dropdown(
            label: "Product of interest",
            info: "Please select a product.",
            id: "product-error",
            options: $options,
            required: true,
            state: "error"
        ); ?>

        <? new Fields\Dropdown(
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
        <? new Cards\ProductCard(
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

        <? new Cards\ProductCard(
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

        <? new Cards\ProductCard(
            title: "Product Card",
            description: "This is a card without an image, without a table and without a label.",
            cta: "Call to action"
        ); ?>
    </div>

    <div style="margin-bottom: 24px;display: flex; gap: 24px;">
        <? new Cards\ContactCard(
            first: "Palau van Helden",
            second: "EKO Instruments B.V.",
            third: "Project Development Engineer",
            email: "palau@vhelden.nl",
            phone: "+31 (0)6 24175606",
            website: "vhelden.nl",
            button: "Ask Palau",
            buttonIcon: "/assets/icons/typing-outline.svg",
            buttonAttributes: [
                "onclick" => "alert('Eventlisteners also work here');"
            ]
        ); ?>

        <? new Cards\ContactCard(
            first: "John Doe",
            second: "Example Corp.",
            third: "Sales Manager",
            email: "john.doe@example.com",
            phone: "+31 (0)6 12345678",
            website: "example.com",
            button: "Contact John",
            buttonIcon: "/assets/icons/envelope-outline.svg",
            image: "assets/images/user-silhouette-" . str_pad(rand(1, 5), 2, "0", STR_PAD_LEFT) . ".svg"
        ); ?>

        <? new Cards\ContactCard(
            first: "Contact Card",
            third: "This contact card has minimal information.",
            email: "contact@example.com",
        ); ?>
    </div>
    
    <div style="display: flex; flex-direction: column; gap: 48px; width: 1024px; margin: 48px 0;">
        <? new SpecTable(
            title: "Specifications",
            rows: [
                "Wavelength Range (nm)" => "285 to 3000",
                "Maximum Operational Irradiance (W/m²)" => "4000",
                "Signal Output" => "Modbus 485 RTU / SDI-12",
                "EMC / Surge Protection Standard" => "IEC61326-1 (Industrial Electromagnetic Environment test level) / IEC61000-4-5 (Level 2, Test Criterion B)",
                "Ingress Protection" => "IP 67",
            ]
        ); ?>

        <? new SpecTable(
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
</body>
</html>