<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're editing an existing page
$page_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$page_title = $page_id ? 'Edit Page' : 'Add New Page';
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    
    <hr class="wp-header-end">
    
    <?php if (isset($_GET['saved']) && $_GET['saved'] == 1) : ?>
    <div class="notice notice-success is-dismissible">
        <p>Page saved successfully.</p>
    </div>
    <?php endif; ?>
    
    <div id="custom-cms-editor" class="custom-cms-container" data-page-id="<?php echo esc_attr($page_id); ?>">
        <form id="custom-cms-form">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <!-- Main Content -->
                    <div id="post-body-content">
                        <div class="postbox">
                            <h2 class="hndle">Basic Information</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="page-slug">Slug</label></th>
                                        <td>
                                            <input type="text" id="page-slug" name="slug" class="regular-text" required>
                                            <p class="description">The slug will be used in the URL. Example: about-us</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="page-h1">H1 Title</label></th>
                                        <td>
                                            <input type="text" id="page-h1" name="h1" class="regular-text">
                                            <p class="description">The main heading of the page (H1)</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="page-intro-text">Intro Text</label></th>
                                        <td>
                                            <?php wp_editor('', 'page-intro-text', [
                                                'textarea_name' => 'intro_text',
                                                'media_buttons' => true,
                                                'textarea_rows' => 5,
                                                'teeny' => false
                                            ]); ?>
                                            <p class="description">Text that appears below the H1 heading</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="page-hero-image">Hero Image</label></th>
                                        <td>
                                            <div class="image-preview-wrapper">
                                                <img id="hero-image-preview" src="" style="max-width: 300px; display: none;">
                                            </div>
                                            <input type="hidden" id="page-hero-image" name="hero_image" class="regular-text">
                                            <button type="button" class="button" id="hero-image-button">Select Image</button>
                                            <button type="button" class="button" id="hero-image-remove" style="display: none;">Remove Image</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Sections -->
                        <div class="postbox">
                            <h2 class="hndle">Content Sections</h2>
                            <div class="inside">
                                <div id="sections-container">
                                    <!-- Section template will be inserted here -->
                                </div>
                                <button type="button" class="button" id="add-section">Add Section</button>
                            </div>
                        </div>
                        
                        <!-- Conclusion Section -->
                        <div class="postbox">
                            <h2 class="hndle">Conclusion</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="conclusion-heading">Heading</label></th>
                                        <td>
                                            <input type="text" id="conclusion-heading" name="conclusion_heading" class="regular-text">
                                            <p class="description">Heading for the conclusion section</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="conclusion-content">Content</label></th>
                                        <td>
                                            <?php wp_editor('', 'conclusion-content', [
                                                'textarea_name' => 'conclusion_content',
                                                'media_buttons' => true,
                                                'textarea_rows' => 5,
                                                'teeny' => false
                                            ]); ?>
                                            <p class="description">Content of the conclusion section</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- FAQ Section -->
                        <div class="postbox">
                            <h2 class="hndle">FAQ Items</h2>
                            <div class="inside">
                                <div id="faq-container">
                                    <!-- FAQ template will be inserted here -->
                                </div>
                                <button type="button" class="button" id="add-faq">Add FAQ Item</button>
                            </div>
                        </div>
                        
                        <!-- Video Components -->
                        <div class="postbox">
                            <h2 class="hndle">Video Components</h2>
                            <div class="inside">
                                <div id="video-container">
                                    <!-- Video component template will be inserted here -->
                                </div>
                                <button type="button" class="button" id="add-video">Add Video Component</button>
                            </div>
                        </div>
                        
                        <!-- Breadcrumbs -->
                        <div class="postbox">
                            <h2 class="hndle">Breadcrumbs</h2>
                            <div class="inside">
                                <div id="breadcrumb-container">
                                    <!-- Breadcrumb template will be inserted here -->
                                </div>
                                <button type="button" class="button" id="add-breadcrumb">Add Breadcrumb</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Publish Box -->
                        <div class="postbox">
                            <h2 class="hndle">Publish</h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="minor-publishing">
                                        <div id="misc-publishing-actions">
                                            <div class="misc-pub-section">
                                                <label for="post_status">Status:</label>
                                                <select name="status" id="post_status">
                                                    <option value="draft">Draft</option>
                                                    <option value="published">Published</option>
                                                </select>
                                            </div>
                                            <div class="misc-pub-section">
                                                <span id="timestamp">Created: <b id="created-date">Not created yet</b></span>
                                            </div>
                                            <div class="misc-pub-section">
                                                <span>Last modified: <b id="modified-date">Not modified yet</b></span>
                                            </div>
                                            <div class="misc-pub-section" id="publish-info" style="display: none;">
                                                <span>Published: <b id="published-date">Not published yet</b></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" id="delete-page-button" style="display: none;">Delete</a>
                                        </div>
                                        <div id="publishing-action">
                                            <button type="button" class="button button-primary button-large" id="save-page">Save</button>
                                            <button type="button" class="button button-primary button-large" id="preview-page" style="display: none;">Preview</button>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SEO Settings -->
                        <div class="postbox">
                            <h2 class="hndle">SEO Settings</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="meta-title">Meta Title</label></th>
                                        <td><input type="text" id="meta-title" name="meta_title" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="meta-desc">Meta Description</label></th>
                                        <td><textarea id="meta-desc" name="meta_desc" rows="3" class="large-text"></textarea></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="og-title">OG Title</label></th>
                                        <td><input type="text" id="og-title" name="og_title" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="og-desc">OG Description</label></th>
                                        <td><textarea id="og-desc" name="og_desc" rows="3" class="large-text"></textarea></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="page-og-image">OG Image</label></th>
                                        <td>
                                            <div class="image-preview-wrapper">
                                                <img id="og-image-preview" src="" style="max-width: 150px; display: none;">
                                            </div>
                                            <input type="hidden" id="page-og-image" name="og_image">
                                            <button type="button" class="button" id="og-image-button">Select Image</button>
                                            <button type="button" class="button" id="og-image-remove" style="display: none;">Remove</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="canonical">Canonical URL</label></th>
                                        <td><input type="text" id="canonical" name="canonical" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="robots">Meta Robots</label></th>
                                        <td>
                                            <select id="robots" name="robots">
                                                <option value="index, follow">index, follow</option>
                                                <option value="noindex, follow">noindex, follow</option>
                                                <option value="index, nofollow">index, nofollow</option>
                                                <option value="noindex, nofollow">noindex, nofollow</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="in-sitemap">Add to Sitemap</label></th>
                                        <td>
                                            <input type="checkbox" id="in-sitemap" name="in_sitemap" value="1" checked>
                                            <label for="in-sitemap">Include this page in the sitemap</label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Structuring Settings -->
                        <div class="postbox">
                            <h2 class="hndle">Structuring</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="region">Region</label></th>
                                        <td>
                                            <select id="region" name="region">
                                                <option value="">Select Region</option>
                                                <option value="country">Country</option>
                                                <option value="state">State</option>
                                                <option value="city">City</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="service">Service</label></th>
                                        <td><input type="text" id="service" name="service" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="sub-service">Sub Service</label></th>
                                        <td><input type="text" id="sub-service" name="sub_service" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="content-type">Content Type</label></th>
                                        <td>
                                            <select id="content-type" name="content_type">
                                                <option value="">Select Content Type</option>
                                                <option value="service">Service</option>
                                                <option value="local">Local</option>
                                                <option value="blog">Blog</option>
                                                <option value="page">Static Page</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="in-header-menu">Add to Header Menu</label></th>
                                        <td>
                                            <input type="checkbox" id="in-header-menu" name="in_header_menu" value="1">
                                            <label for="in-header-menu">Include this page in the header menu</label>
                                        </td>
                                    </tr>
                                    <tr>
    <th scope="row"><label for="category">Category</label></th>
    <td>
        <select id="category" name="category_id" class="regular-text">
            <option value="">Select Category</option>
            <!-- Categories will be loaded via JavaScript -->
        </select>
    </td>
</tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Author Settings -->
                        <div class="postbox">
                            <h2 class="hndle">Authoring</h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="author-name">Author Name</label></th>
                                        <td><input type="text" id="author-name" name="author_name" class="regular-text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Editor</th>
                                        <td><span id="editor-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Templates -->
<!-- Section Template -->
<script type="text/template" id="section-template">
    <div class="section-item" data-index="{{index}}">
        <div class="section-header">
            <h3>Section {{index}}</h3>
            <div class="section-actions">
                <button type="button" class="button move-up">↑</button>
                <button type="button" class="button move-down">↓</button>
                <button type="button" class="button button-link-delete delete-section">Remove</button>
            </div>
        </div>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="section-heading-{{index}}">Heading</label></th>
                <td><input type="text" id="section-heading-{{index}}" name="sections[{{index}}][heading]" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="section-content-{{index}}">Content</label></th>
                <td>
                    <textarea id="section-content-{{index}}" name="sections[{{index}}][content]" class="section-content" rows="5"></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="section-anchor-{{index}}">Anchor ID</label></th>
                <td>
                    <input type="text" id="section-anchor-{{index}}" name="sections[{{index}}][anchor]" class="regular-text">
                    <p class="description">Used for fragment URLs like #this-section</p>
                </td>
            </tr>
        </table>
    </div>
</script>

<!-- FAQ Template -->
<script type="text/template" id="faq-template">
    <div class="faq-item" data-index="{{index}}">
        <div class="faq-header">
            <h3>FAQ Item {{index}}</h3>
            <div class="faq-actions">
                <button type="button" class="button move-up">↑</button>
                <button type="button" class="button move-down">↓</button>
                <button type="button" class="button button-link-delete delete-faq">Remove</button>
            </div>
        </div>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="faq-question-{{index}}">Question</label></th>
                <td><input type="text" id="faq-question-{{index}}" name="faq_items[{{index}}][question]" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="faq-answer-{{index}}">Answer</label></th>
                <td>
                    <textarea id="faq-answer-{{index}}" name="faq_items[{{index}}][answer]" class="faq-answer" rows="5"></textarea>
                </td>
            </tr>
        </table>
    </div>
</script>

<!-- Video Component Template -->
<script type="text/template" id="video-template">
    <div class="video-item" data-index="{{index}}">
        <div class="video-header">
            <h3>Video Component {{index}}</h3>
            <div class="video-actions">
                <button type="button" class="button move-up">↑</button>
                <button type="button" class="button move-down">↓</button>
                <button type="button" class="button button-link-delete delete-video">Remove</button>
            </div>
        </div>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="video-heading-{{index}}">Heading</label></th>
                <td><input type="text" id="video-heading-{{index}}" name="video_components[{{index}}][heading]" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="video-text-{{index}}">Description</label></th>
                <td>
                    <textarea id="video-text-{{index}}" name="video_components[{{index}}][text]" class="video-text" rows="3"></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="video-embed-{{index}}">Embed Code</label></th>
                <td>
                    <textarea id="video-embed-{{index}}" name="video_components[{{index}}][embed]" class="video-embed" rows="3"></textarea>
                </td>
            </tr>
        </table>
    </div>
</script>

<!-- Breadcrumb Template -->
<script type="text/template" id="breadcrumb-template">
    <div class="breadcrumb-item" data-index="{{index}}">
        <div class="breadcrumb-header">
            <h3>Breadcrumb {{index}}</h3>
            <div class="breadcrumb-actions">
                <button type="button" class="button move-up">↑</button>
                <button type="button" class="button move-down">↓</button>
                <button type="button" class="button button-link-delete delete-breadcrumb">Remove</button>
            </div>
        </div>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="breadcrumb-text-{{index}}">Text</label></th>
                <td><input type="text" id="breadcrumb-text-{{index}}" name="breadcrumbs[{{index}}][text]" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="breadcrumb-url-{{index}}">URL</label></th>
                <td><input type="text" id="breadcrumb-url-{{index}}" name="breadcrumbs[{{index}}][url]" class="regular-text"></td>
            </tr>
        </table>
    </div>
</script>