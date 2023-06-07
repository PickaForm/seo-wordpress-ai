/**
 * 
 * SEO Wordpress AI | By Team DnL
 * 
 * Javascript UI built on top of KissJS - Keep It Simple Stupid Javascript
 * https://kissjs.net
 * 
 */

//
// Static messages
//
const MSG_WARNING_SAVE_TITLE = "Did you save your modifications before proceeding?";
const MSG_WARNING_SAVE = "Please ensure your page is saved before proceeding. Have you saved your page?";

const MSG_WARNING_SLUG_TITLE = "Modifying the slug is dangerous";
const MSG_WARNING_SLUG = `Are you sure you want to edit the slug?
Editing the slug after the post has been published could affect your page\'s SEO negatively.
If you are not 100% sure about the effect of this action, please cancel the action.`;

const MSG_CONFIRM_SLUG = "Yes, I really need to update the slug!";
const MSG_ERROR_SLUG = "Sorry, we couldn't update your slug for an unknow reason";

const MSG_WARNING_OPENAI_ERROR_TITLE = "OpenAI API key error";
const MSG_WARNING_OPENAI_ERROR = `Your OpenAI key is not valid.
Please check that you entered your OpenAI key in SEO Wordpress AI settings page.
If you already did it, there was maybe an error while copy/pasting your key.

<a href="/wp-admin/options-general.php?page=swa">Click here to enter your OpenAI API key</a>`;

const MSG_WARNING_OPENAI_QUOTA_ERROR_TITLE = "Quota, rate limit or overload";
const MSG_WARNING_OPENAI_QUOTA_ERROR = `OpenAi returned an error '429', which has 3 possible causes:

1 - Rate limit reached for requests
Cause: You are sending requests too quickly.

2 - You exceeded your current quota, please check your plan and billing details
Cause: You have hit your maximum monthly spend (hard limit) which you can view in the account billing section.

3 - The engine is currently overloaded, please try again later
Cause: OpenAI servers are experiencing high traffic.

<a href="https://platform.openai.com/docs/guides/error-codes/api-errors">Click here to view OpenAI documentation</a>`;

const MSG_WARNING_CHATGPT_ERROR_TITLE = "ChatGPT answer is not correct";
const MSG_WARNING_CHATGPT_ERROR = `The response of ChatGPT is not correct.
This can happen for various reasons.
Most of the time, it\'s because it's partly random and sometimes does not return the expected data format.
Please, try again.
If the problem persists, please, also check if OpenAI servers are up.`;

const MSG_CHAT_GPT_RESULTS = `Please take into consideration that we are making a request to OpenAI and expecting
results in a specific format.
Because the nature of the AI is partly random, the results returned may not be able to
be interpreted correctly by our plugin. In this case, it is necessary to restart a request.`

const MSG_DONE = "Done! Now reloading the page...";

/**
 * Insert buttons after the DOM is fully loaded
 */
window.onload = function () {
    const seoFieldsButton = $("swa_seo_fill_button");
    const slugButton = $("swa_slug_button");
    const openAIKeyField = $("swa_api_key");
    const openAIEncryptionKeyField = $("swa_encryption_key");
    const submitOpenAIKeyButton = $("swa-submit");

    /**
     * Check if fields are correct in the settings page
     */
    if (openAIKeyField && openAIEncryptionKeyField && submitOpenAIKeyButton) {
        submitOpenAIKeyButton.onclick = function (event) {
            if (openAIKeyField.value == "") {
                createNotification({
                    message: "Please, enter your OpenAI key before saving your settings",
                    duration: 3000
                })
                event.stop();
            }

            if (openAIEncryptionKeyField.value == "") {
                createNotification({
                    message: "Please, enter a secret phrase to encrypt your OpenAI key",
                    duration: 3000
                })
                event.stop();
            }
        }
    }

    if (seoFieldsButton && slugButton) {
        const wpseo = document.querySelector(".wpseo-metabox-menu");
        seoFieldsButton.show();
        wpseo.appendChild(seoFieldsButton);

        // Future button to force slug update - If needed
        // slugButton.show();
        // wpseo.appendChild(slugButton);
    } else return

    /**
     * Handle SEO auto-fill button
     */
    seoFieldsButton.onclick = function () {
        msgWarningSave(MSG_WARNING_SAVE_TITLE, MSG_WARNING_SAVE, async () => {

            createNotification({
                message: MSG_CHAT_GPT_RESULTS,
                maxWidth: 600,
                duration: 5000
            }).style.zIndex = 10000

            const postId = this.getAttribute("data-post-id");
            const response = await kiss.ajax.request({
                url: ajaxurl,
                method: "post",
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                showLoading: true,
                body: new URLSearchParams({
                    action: "swa_fill_seo_fields",
                    post_ID: postId
                })
            })

            if (response.success === false) {
                if (response.data == "401") {
                    // Authentication error
                    msgError(MSG_WARNING_OPENAI_ERROR_TITLE, MSG_WARNING_OPENAI_ERROR);
                } else if (response.data == "429") {
                    // Quota, rate limit, or overload error
                    msgError(MSG_WARNING_OPENAI_QUOTA_ERROR_TITLE, MSG_WARNING_OPENAI_QUOTA_ERROR);
                } else {
                    // Other errors, relative to badly generated content
                    msgError(MSG_WARNING_CHATGPT_ERROR_TITLE, MSG_WARNING_CHATGPT_ERROR);
                }
            } else {
                createNotification({
                    message: MSG_DONE,
                    duration: 10000
                }).style.zIndex = 10000

                location.reload();
            }
        })
    }

    /**
     * Handle slug button
     */
    slugButton.onclick = function () {

        msgWarningSave(MSG_WARNING_SAVE_TITLE, MSG_WARNING_SAVE, () => {

            createDialog({
                type: "danger",
                title: MSG_WARNING_SLUG_TITLE,
                message: MSG_WARNING_SLUG,
                buttonOKText: MSG_CONFIRM_SLUG,
                action: async () => {
                    const postId = this.getAttribute("data-post-id");
                    const focuskwField = document.getElementById("focus-keyword-input-metabox");
                    const focuskw = focuskwField.value;
                    const slug = slugify(focuskw);

                    const response = await kiss.ajax.request({
                        url: ajaxurl,
                        method: "post",
                        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                        showLoading: true,
                        body: new URLSearchParams({
                            action: 'swa_update_post_slug',
                            post_ID: postId,
                            new_focuskw: focuskw,
                            new_slug: slug
                        })
                    })

                    if (response.success === false) {
                        createNotification({
                            message: MSG_ERROR_SLUG,
                            duration: 3000
                        }).style.zIndex = 10000
                    } else {
                        createNotification({
                            message: MSG_DONE,
                            duration: 10000
                        }).style.zIndex = 10000

                        location.reload();
                    }
                }
            }).setAnimation({
                name: "zoomIn",
                speed: "faster"
            }).style.zIndex = 10000
        })
    }

    /**
     * Utility function to display a warning window
     * 
     * @param {string} title 
     * @param {string} message 
     */
    const msgError = (title, message) => {
        createDialog({
            type: "message",
            title,
            message
        }).style.zIndex = 10000
    }

    /**
     * Utility function to warn the user about saving his page prior to processing
     * 
     * @param {string} title 
     * @param {string} message 
     * @param {function} callback 
     */
    const msgWarningSave = (title, message, callback) => {
        createDialog({
            id: "warning-save",
            type: "danger",
            title,
            message,
            buttonOKText: "Yes",
            buttonCancelText: "Not yet",
            action: () => {
                $("warning-save").close();
                callback();
            }
        }).setAnimation({
            name: "rotateIn",
            speed: "faster"
        }).style.zIndex = 10000
    }

    /**
     * Utility function to generate a slug
     * 
     * @param {string} text 
     * @returns {string} slugified text
     */
    function slugify(text) {
        return text.toString().toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9\-]/g, "")
            .replace(/\-\-+/g, "-")
            .replace(/^-+/, "")
            .replace(/-+$/, "");
    }
};