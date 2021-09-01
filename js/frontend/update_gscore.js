/**
 * SAVE OR UPLDATE GSCORE 
 *`gscore` is needed to enable anonymus users to view
 * Gravity Scores content. It contains form and entry
 * information from Gravity Forms.
 */
let gScoreToLocalStorage = function () {

    // from url
    let url = new URL(document.URL);
    let gscore = url.searchParams.get('gscore')

    // from hidden field
    if (!(typeof gscore === 'string' || gscore instanceof String)) {

        hidden_field = document.querySelector('#gscore')

        if (hidden_field !== null) {
            gscore = hidden_field.value
        }
    }

    // add or overwrite to local storage
    if (typeof gscore === 'string' || gscore instanceof String) {

        let values = atob(gscore).split(' ')[0].split('::');
        localStorage.setItem('gscore' + values[0], values[1]);
        return 'gscore' + values[0]
    }

    return false

}

// RUN SCRIPT
document.addEventListener('DOMContentLoaded', () => {

    gScoreToLocalStorage()

})




