<?php

?>

<style>
    .eb-content-wrapper-container {
        padding: 45px;
        background-color: #FFFFFF;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0px 6px 20px 0px #0000001A;
    }
    .eb-content-wrapper {
        padding: 20px;
        width: 100%;
        box-sizing: border-box;
        max-height: 600px;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        position: relative;
    }

    .eb-content-wrapper:not(.expanded)::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 200px;
        background: linear-gradient(
            to bottom,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.8) 50%,
            rgba(255, 255, 255, 0.95) 75%,
            rgba(255, 255, 255, 1) 100%
        );
        pointer-events: none;
    }

    .eb-content-wrapper.expanded {
        max-height: none;
    }

    .eb-content-wrapper.expanded::after {
        display: none;
    }

    .eb-section {
        background-color: #ffffff;
        border: 1px solid #e2e4e7;
        border-radius: 2px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .eb-section-title {
        color: #00b1c3 !important;
        font-size: 16px;
        margin: 0;
        padding: 20px;
        background-color: #ffffff;
        border-bottom: 1px solid #e2e4e7;
    }

    .eb-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }

    .eb-table th {
        padding: 15px 20px;
        text-align: left;
        border-bottom: 1px solid #e2e4e7;
        font-weight: 500;
        color: #23282d;
        background-color: #EDEDED;
    }

    .eb-table td {
        padding: 15px 20px;
        text-align: left;
        border-bottom: 1px solid #e2e4e7;
        color: #50575e;
    }

    /* Alternating row colors */
    .eb-table tr:nth-child(odd) td {
        background-color: #F8FBFC;
    }

    .eb-table tr:nth-child(even) td {
        background-color: #ffffff;
    }

    /* Impact column styling */
    .eb-table th.impact-header {
        background-color: #DDF9C8 !important;
    }

    .eb-table td:last-child {
        box-shadow: 0px 6px 20px 0px #0000001A;
        position: relative;
        background-color: #f2fffd !important;
    }

    /* Column widths */
    .eb-table th:first-child,
    .eb-table td:first-child {
        width: 25%;
    }

    .eb-table th:nth-child(2),
    .eb-table td:nth-child(2) {
        width: 45%;
    }

    .eb-table th:last-child,
    .eb-table td:last-child {
        width: 30%;
    }

    .eb-main-title {
        color: #23282d;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 24px;
        padding: 0;
    }

    .ebsection {
        text-align: right;
    }

    .ebsection .collapsed-btn {
        padding: 10.5px 12px;
        color: #FFF;
        font-size: 14px;
        font-weight: 600;
    }

    .eb-show-more {
        text-align: center;
        padding: 20px;
        cursor: pointer;
        color: #ff5722;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        background: transparent;
        border: none;
        width: 100%;
        margin: 0;
        position: relative;
        z-index: 2;
    }

    .eb-show-more:hover {
        color: #ff7043;
    }

    .eb-show-more svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
        transition: transform 0.3s ease;
    }

    .eb-show-more.expanded svg {
        transform: rotate(180deg);
    }
</style>

<div class="eb-content-wrapper-container">
    <h2 class="eb-main-title">Bridge PRO Add-ons Impact</h2>
    <div class="eb-content-wrapper">
        <div class="eb-section">
            <h3 class="eb-section-title">WooCommerce Integration</h3>
            <table class="eb-table">
                <tr>
                    <th>Challenges/Tasks</th>
                    <th>Edwiser Bridge Solution</th>
                    <th class="impact-header">Impact</th>
                </tr>
                <tr>
                    <td>Subscription management</td>
                    <td>Sell recurring subscriptions</td>
                    <td>27% revenue growth</td>
                </tr>
                <tr>
                    <td>Course bundling struggles</td>
                    <td>Sell discounted bundles</td>
                    <td>35% higher revenue</td>
                </tr>
                <tr>
                    <td>Course expiration tracking</td>
                    <td>Auto-remove learners after subscription ends</td>
                    <td>5 hr/week → automated</td>
                </tr>
                <tr>
                    <td>Delayed enrollment updates</td>
                    <td>Instant sync of course access</td>
                    <td>100% real-time access</td>
                </tr>
            </table>
        </div>

        <div class="eb-section">
            <h3 class="eb-section-title">Single Sign On</h3>
            <table class="eb-table">
                <tr>
                    <th>Challenges/Tasks</th>
                    <th>Edwiser Bridge Solution</th>
                    <th class="impact-header">Impact</th>
                </tr>
                <tr>
                    <td>Disjointed WordPress-Moodle logins</td>
                    <td>Unified Login For Both WordPress & Moodle</td>
                    <td>Fewer Login Issues</td>
                </tr>
                <tr>
                    <td>Password reset overload</td>
                    <td>Social login</td>
                    <td>Less Password Stress</td>
                </tr>
            </table>
        </div>

        <div class="eb-section">
            <h3 class="eb-section-title">Selective Sync</h3>
            <table class="eb-table">
                <tr>
                    <th>Challenges/Tasks</th>
                    <th>Edwiser Bridge Solution</th>
                    <th class="impact-header">Impact</th>
                </tr>
                <tr>
                    <td>Outdated course listings</td>
                    <td>Selective sync</td>
                    <td>80% faster sync</td>
                </tr>
            </table>
        </div>

        <div class="eb-section">
            <h3 class="eb-section-title">Bulk Purchase</h3>
            <table class="eb-table">
                <tr>
                    <th>Challenges/Tasks</th>
                    <th>Edwiser Bridge Solution</th>
                    <th class="impact-header">Impact</th>
                </tr>
                <tr>
                    <td>Manual user enrollment</td>
                    <td>Bulk CSV upload</td>
                    <td>8 hr → 2 min</td>
                </tr>
                <tr>
                    <td>Corporate bulk purchases</td>
                    <td>Sell bulk course seats at once</td>
                    <td>75% faster onboarding</td>
                </tr>
            </table>
        </div>
        <?php if ( ! class_exists( '\app\wisdmlabs\edwiserBridgePro\includes\Edwiser_Bridge_Pro' ) ) { ?>
        <div class="ebsection">
            <a href="https://edwiser.org/edwiser-bridge-pro/?utm_source=inproduct&utm_medium=impact_section&utm_campaign=wordpress_bridge_listing" class="eb-pro-upgrade-to-pro-btn collapsed-btn" target="_blank">Upgrade to PRO</a>
        </div>
        <?php } ?>
    </div>
    <div class="eb-show-more">
        Show more
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
        </svg>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.eb-content-wrapper');
    const showMoreBtn = document.querySelector('.eb-show-more');
    
    wrapper.style.maxHeight = '600px';
    
    showMoreBtn.addEventListener('click', function() {
        if (wrapper.classList.contains('expanded')) {
            wrapper.classList.remove('expanded');
            wrapper.style.maxHeight = '600px';
            this.innerHTML = 'Show more <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg>';
        } else {
            wrapper.classList.add('expanded');
            wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
            this.innerHTML = 'Show less <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/></svg>';
        }
        this.classList.toggle('expanded');
    });
});
</script>
