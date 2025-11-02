# Backstop Generator Documentation
## Overview


Backstop Generator helps you build and manage visual regression tests for your Drupal site using BackstopJS. This module simplifies the process of creating test scenarios, configuring viewports, and running tests to ensure your site's visual integrity across different devices and screen sizes. Changes you make in the interface will be reflected in the BackstopJS JSON file, allowing you to easily manage and run your visual regression tests.

# =========================

## Requirements

- Drupal 10 or 11
- PHP 8.1 or higher
- Node.js and npm
- BackstopJS (install globally via `npm install -g backstopjs`)

# =========================

## Installation

1. Install the Backstop Generator module:
    - With Composer: `composer require drupal/backstop_generator`
    - Or download and place into your site's `modules/contrib` directory
2. Enable the module and rebuild caches:
   ```bash
   drush en backstop_generator -y
   drush cr
   ```
3. Install BackstopJS globally:
   ```bash
   npm install -g backstopjs
   ```
4. (Optional) Create the scripts directory for custom BackstopJS scripts:
   ```bash
   mkdir -p tests/backstop/backstop_data/scripts
   ```
5. Configure the module via the Drupal admin UI: `/admin/config/development/backstop-generator`.
6. Generate your BackstopJS scenarios from the Profiles tab and run the displayed BackstopJS commands.

# =========================

## Terminology


* **Profile:** A collection of test scenarios. A Profile also defines the Viewports used during testing. Each Profile can include multiple Viewports and Scenarios.
* **Scenario:** Represents a single page on your site that you want to test. Every Scenario is associated with one Profile.
* **Scenario Defaults:** A set of configuration options at the Profile level that apply to all associated Scenarios, unless a Scenario overrides them.
* **Viewport:** Defines the browser window size used during testing. Most commonly set to mobile, tablet, and desktop breakpoints.

# =========================

## How to Use This Module


### 1. Configure the Module


1. Navigate to: *Configuration > Development > Backstop Generator* (`/admin/config/development/backstop-generator`)
2. The **Test domain** will default to your current site URL.
3. In the **Reference domain** field, enter the URL of the site to compare against (usually your live production site).
4. Use **Scenario Defaults** and **Profile Parameters** to define default values applied to new Profiles. The default values provided can be edited later per Profile.

---
### 2. Configure Your Viewports


* Click the **Viewports** tab.
* Viewports are automatically created based on the breakpoints in your site's default theme.
* To add Viewports for other enabled themes with breakpoints, use the **Viewport Generator**.
* You can also manually add a new Viewport by clicking **Add Viewport** in the upper-right corner.

---
### 3. Create Your First Profile


1. Click on the **Profile** tab.
2. Click **Add Backstop Profile**.
3. Provide a title and description explaining what this Profile will test.
4. Select the **Viewports** to be used.
5. Set **Profile Parameters** and **Scenario Defaults**.
6. Scroll to the **Scenario Generator** section:
    * **Languages:** Choose which enabled languages to test.
    * **Homepage:** Check this to include your site’s homepage.
    * **Menus:** Select the menus from which to generate Scenarios. Use the **Menu Depth** setting to define how many levels deep to include.
    * **Content Types:** Automatically generate a set number of random nodes per content type (e.g., 5 Blog, 5 Article).
    * **Nodes and Paths:**
        + Select specific nodes using the **Node Lookup** field.
        + Manually add paths (e.g., Views or search pages) with the format: `label | path`
7. Click **Save and generate scenarios** to save the Profile and generate the Scenarios.
8. Select the Scenarios you want to include, then click **Save**.
9. A BackstopJS JSON file will be generated for the Profile.


🎉 Congratulations! You’ve created your first visual regression test with BackstopJS.

---
### 4. Run the Commands


1. Go to the **Commands** tab.
2. Copy and run the displayed commands in your terminal:
    * Run the **reference** command to generate baseline images.
    * Run the **test** command to compare your current site with the reference version.

---
### 5. Edit Your Scenarios


If a Scenario fails, you can adjust its configuration:


* Example: Hide a slideshow by adding its selector to the **hideSelectors** field.


Use the text filter at the top of the Scenarios page to quickly locate specific items.

# =========================

## Module Configuration


### Settings Tab


* **Test Domain**: This field is automatically populated with your current site URL. It is the site you want to test.
* **Reference Domain**: This field is where you enter the URL of the site to compare against, typically your live production site.
* **Scenario Defaults**: Settings to include in the scenarioDefaults section. These can be overridden at the Profile level. All new Profiles you create will inherit these defaults.
* **Profile Parameters**: Default settings that can be overridden at the Profile level. All new Profiles you create will inherit these defaults.


⚠️ NOTE: The settings you configure here will be applied to new Profiles only. Existing Profiles will retain their individual settings unless manually updated.

---

### Viewports Tab


Viewports are automatically generated based on the breakpoints in your site's default theme. You can also manually add new Viewports or use the Viewport Generator to create them for other enabled themes with breakpoints.


#### Viewport Generator


Backstop Generator can automatically create Viewports for any enabled theme that has configured breakpoints. To do this, select the theme from the list and click the **Generate viewports** button. This will generate Viewports based on the breakpoints defined in the theme's [theme].breakpoints.yml file.


---

### Profiles Tab


The Profiles tab allows you to create and manage your BackstopJS Profiles. Each Profile contains a set of Scenarios and can have its own configuration settings. The default settings are inherited from the **Profile parameters** section on the module configuration form. Below are the key parameters you can configure for each Profile.


#### Scenario Generator


Within each profile, the Scenario Generator allows you to automatically create Scenarios based on your site's content. You can select which languages to test, include the homepage, and choose specific menus and content types to generate Scenarios from. You can also manually add paths for specific nodes or views.

---

### Scenarios Tab



The Scenarios tab allows you to manage individual test scenarios for your BackstopJS Profiles. Each Scenario represents a specific page of your site that you want to visually test. Below are the key settings you can configure for each Scenario. Settings here will override those set globally in the Profile's **scenarioDefaults** section.



---

### Commands Tab

The Commands tab scans the folder where Backstop's JSON files are stored, and displays the terminal commands you need to run the tests. Run these commands into your terminal to generate reference images and run tests against your current site.

# =========================

## Field Glossary

---
### asyncCaptureLimit


**Description:** Maximum number of concurrent screenshot captures.

**Usage:** Control parallel processing to manage system resources.

**Recommended Values:**

* Low-resource systems: `2-3`
* High-performance systems: `5-10`

---
### asyncCompareLimit


**Description:** Maximum number of concurrent image comparisons.

**Usage:** Manage parallel processing during test result comparisons.

**Recommended Values:**

* Similar to `asyncCaptureLimit`
* Adjust based on available system resources

---
### clickSelector / clickSelectors


**Description:** CSS selector(s) to click before screenshot.

**Usage:** Open modals, expand sections, or trigger interactions.

**Example Configurations:**

* Single selector: `"#open-dialog-btn"`
* Multiple selectors: `["#tab-1", "#accordion-trigger"]`

---
### cookiePath


**Description:** Path to a JSON file containing cookies to be loaded for the scenario.

**Usage:** Simulate authenticated or pre-configured browser states.

**Example Configuration:**

```
{
  "cookiePath": "backstop_data/cookies/user_session.json"
}
```

**Best Practices:**

* Use absolute or relative paths
* Ensure cookie file contains valid JSON
* Protect sensitive cookie information

---
### debug


**Description:** Enables detailed logging and debugging information.

**Usage:** Troubleshoot test configuration and capture issues.

**Options:**

* `true`: Enable verbose logging
* `false`: Standard logging (default)

---
### debugWindow


**Description:** Opens a browser window during testing for visual debugging.

**Usage:** Inspect page state and capture process in real-time.

**Options:**

* `true`: Open browser window during tests
* `false`: Run tests in headless mode (default)

---
### delay


**Description:** Additional wait time after page load before capturing screenshot.

**Usage:** Allow for animations, transitions, or dynamic content.

**Unit:** Milliseconds

**Example Configurations:**

* `500`: Half-second delay
* `2000`: Two-second delay for complex animations

---
### engine


**Description:** Specifies the browser engine for running tests.

**Usage:** Select the rendering engine for screenshot and comparison.

**Options:**

* `"puppeteer"`: Chromium-based engine (recommended)
* `"playwright"`: Cross-browser testing engine
* `"chromy"`: Older Chromium-based engine

---
### engineOptions


⚠️ NOTE: This field is not currently available in the Backstop Generator UI. The default is --no-sandbox.

**Description:** Advanced configuration for the selected testing engine.

**Usage:** Customize browser behavior and performance.

**Example Configuration:**

```
{
  "args": ["--no-sandbox"],
  "headless": true,
  "timeout": 30000
}
```
---
### expect


**Description:** The number of selector matches expected on the page.

**Usage:** Validate the presence of a specific number of matching elements.

**Type:** Integer

**Example Configurations:**

* `0`: Ensure no elements match the selector
* `1`: Verify exactly one element exists
* `5`: Check for exactly 5 matching elements


**Use Cases:**


* Confirm consistent page structure
* Validate dynamic content generation
* Ensure specific components are present

**Example:**

```
{
  "selectors": [".product-card"],
  "expect": 4
}

```

This configuration checks that exactly 4 product cards are present on the page.

---
### gotoParameters


**Description:** Advanced navigation parameters for scenario.

**Usage:** Configure page load and navigation behavior.

**Example Configuration:**

```
{
  "timeout": 30000,
  "waitUntil": "networkidle0"
}
```
---
### hideSelectors


**Description:** CSS selectors of elements to hide before screenshot.

**Usage:** Remove dynamic or distracting page elements.

**Example Configurations:**

```
[
  "#floating-chat",
  ".advertisement",
  "[data-testid='cookie-banner']"
]
```
---
### hoverSelector / hoverSelectors


**Description:** CSS selector(s) to hover over before screenshot.

**Usage:** Reveal dropdown menus, tooltips, or hover states.

**Example Configurations:**

* Single selector: `"#user-menu"`
* Multiple selectors: `["#nav-item-1", "#nav-item-2"]`

---
### keyPressSelectors


**Description:** Simulate keyboard interactions before screenshot.

**Usage:** Trigger form interactions or page state changes.

**Example Configuration:**

```
[
  {"selector": "#search-input", "keyPress": "Enter"},
  {"selector": "#username", "keyPress": "Tab"}
]
```
---
### misMatchThreshold


**Description:** Percentage of pixel difference allowed before test fails.

**Usage:** Control sensitivity of visual comparison.

**Range:** `0-100`

**Recommended Values:**

* `0`: Exact pixel match
* `0.1`: Minimal tolerance
* `5`: Slight variations allowed

---
### onBeforeScript


⚠️ NOTE: Backstop Generator currently does not allow a custom file to be named. The default is `onBefore.js` and it must be placed in the `tests/backstop/backstop_data/scripts` folder.

The `onBeforeScript` field is a boolean value in the UI that indicates whether to run a custom script.

**Description:** A custom script that runs before capturing screenshots.

**Usage:** Use to prepare the page state, log in, dismiss modals, or perform any necessary setup.

**Example Use Cases:**

* Logging into an admin interface
* Clearing browser cookies
* Setting up specific page conditions
* Navigating to a specific page before screenshot

---
### onReadyScript


⚠️ NOTE: Backstop Generator currently does not allow a custom file to be named. The default is `onReady.js` and it must be placed in the `tests/backstop/backstop_data/scripts` folder.

The `onReadyScript` field is a boolean value in the UI that indicates whether to run a custom script.

**Description:** A script that runs after the page loads but before capturing the screenshot.

**Usage:** Handle dynamic content, wait for animations, or ensure page stability.

**Example Use Cases:**

* Waiting for AJAX content to load
* Dismissing cookie consent banners or modal dialogs
* Pausing to allow animations to complete
* Scrolling to specific page elements

---
### paths


⚠️ NOTE: This field is not currently available in the Backstop Generator UI.<br>The profile's ID is added to the path when the Backstop JSON file is generated providing a unique path for each Profile's test and reference files.

**Description:** Specifies the directory paths for storing BackstopJS test results and artifacts.

**Usage:** Define the locations for test output, including screenshots, reports, and comparison images.

**Configuration Options:**

* `bitmaps_reference`: Directory to store baseline (reference) screenshots
* `bitmaps_test`: Directory to store test run screenshots
* `reports`: Directory for generating test reports
* `html_report`: Path for generating HTML-based test reports
* `ci_report`: Path for Continuous Integration compatible reports

**Example Configuration:**

```
{
  "bitmaps_reference": "backstop_data/bitmaps_reference",
  "bitmaps_test": "backstop_data/bitmaps_test",
  "reports": "backstop_data/reports",
  "html_report": "backstop_data/html_report",
  "ci_report": "backstop_data/ci_report"
}
```

**Best Practices:**

* Use relative paths from your project root
* Ensure the specified directories exist before running tests
* Use consistent naming conventions
* Exclude these directories from version control (add to .gitignore)

---
### postInteractionWait


**Description:** Time to wait after interactions before capturing screenshot.

**Usage:** Allow page to stabilize after clicks or key presses.

**Unit:** Milliseconds

**Example Configurations:**

* `1000`: Wait one second after interaction
* `3000`: Extended wait for complex interactions

---
### readyEvent


**Description:** Custom browser event to wait for before capturing screenshot.

**Usage:** Ensure page is fully loaded and interactive.

**Example Configurations:**

* `"custom-page-ready"`
* `"DOMContentLoaded"`
* `"load"`

---
### readySelector


**Description:** CSS selector to wait for before taking screenshot.

**Usage:** Ensure specific element is present before capture.

**Example Configurations:**

* `"#main-content"`
* `.loading-complete`
* `[data-ready="true"]`

---
### readyTimeout


**Description:** Maximum time to wait for ready conditions.

**Usage:** Prevent tests from hanging on slow-loading pages.

**Default:** `10000` (10 seconds)

**Example Configurations:**

* `5000`: Wait up to 5 seconds
* `30000`: Extended wait for complex pages

---
### referenceUrl


**Description:** The authoritative URL from which the baseline (reference) screenshot will be captured.

**Usage:** Specify the source of truth for visual comparison across different environments.

**Example Use Cases:**

* Compare staging environment against production
* Validate visual consistency across different deployment environments
* Test responsive design across development, staging, and production


**Key Considerations:**

* The `referenceUrl` should contain the canonical or "correct" version of the page.
* Useful for:
    + Comparing different environments
    + Tracking visual changes during development
    + Ensuring visual consistency across different platforms

---
### removeSelectors


**Description:** CSS selectors of elements to completely remove from page.

**Usage:** Eliminate specific DOM elements before capture.

**Example Configurations:**

```
[
  "#live-updates",
  ".dynamic-content",
  "iframe.external-widget"
]
```
---
### report


⚠️ NOTE: This field is not currently available in the Backstop Generator UI. It is set to `"browser"` by default in the BackstopJS JSON file.

**Description:** Configures the output format and location of test reports.

**Usage:** Choose report types and specify output directories.

**Options:**

* `"browser"`: Open report in default browser
* `"CI"`: Generate report for Continuous Integration systems
* `["browser", "CI"]`: Generate multiple report types

---
### requireSameDimensions


**Description:** Enforce identical screenshot dimensions.

**Usage:** Ensure consistent element sizes across comparisons.

**Options:**

* `true`: Require exact dimension match
* `false`: Allow slight dimension variations

---
### scenarioDefaults


**Description:** Default settings applied to all test scenarios.

**Usage:** Set common configurations to reduce repetition in individual scenario definitions.

**Example Configuration:**

```
{
  "hideSelectors": [".advertisement"],
  "removeSelectors": ["#cookie-banner"],
  "delay": 500
}
```
---
### scrollToSelector


**Description:** CSS selector to scroll to before capturing screenshot.

**Usage:** Ensure specific content is in view.

**Example Configurations:**

* `"#main-content"`
* `.section-to-verify`
* `[data-scroll-target="true"]`

---
### selectorExpansion


**Description:** Automatically expand selectors to capture all matching elements.

**Usage:** Capture multiple instances of a selector type.

**Options:**

* `true`: Capture all matching elements
* `false`: Capture only first matching element

---
### selectors


**Description:** CSS selectors of specific page elements to capture.

**Usage:** Focus on particular regions instead of full page.

**Example Configurations:**

```
[
  "#main-content",
  ".product-grid",
  "header",
  "footer"
]
```
---
### url


**Description:** The specific web page URL to be tested in this scenario.

**Usage:** Define the exact page or route to capture.

**Example Configurations:**

* Full URL: `"https://example.com/about"`
* Relative path: `"/products/category"`
* Local development: `"http://localhost:3000/dashboard"`

---
### viewports

⚠️ NOTE: Backstop Generator currently only supports setting viewports globally in the Profile.

Viewports can be set globally in the **Profile** or overridden in the **Scenario**.

**Description:** Defines the screen sizes and device dimensions for testing.

**Usage:** Specify an array of viewport configurations to test your site's responsiveness.

**Example Configurations:**

* Desktop: `{"label": "desktop", "width": 1920, "height": 1080}`
* Tablet: `{"label": "tablet", "width": 768, "height": 1024}`
* Mobile: `{"label": "mobile", "width": 375, "height": 667}`

