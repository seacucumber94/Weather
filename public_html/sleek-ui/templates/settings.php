<?php
// settings.php - Settings page for sleek-ui
require_once 'includes/header.php';
?>
<main>
    <section>
        <h2>Settings</h2>
        <form>
            <label for="theme">Theme:</label>
            <select id="theme" name="theme">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
            </select>
            <br /><br />
            <button type="submit">Save Settings</button>
        </form>
    </section>
</main>
<?php
require_once 'includes/footer.php';
?>