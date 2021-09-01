import Marked from 'marked'

function replace_log(data_url) {
    axios.get(data_url).then(function (response) {
        document.querySelector('.log-entry-content').innerHTML = Marked(response.data)
    })
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.log-entry').forEach(function (elem) {
        elem.addEventListener('click', function (event) {
            document.querySelectorAll('.log-entry').forEach(elem => {
                elem.classList.remove('active')
            })
            this.classList.add('active');

            replace_log(this.getAttribute('data-url'))

        })
    })

    if (document.querySelectorAll('.log-entry').length != 0) {
        let start_log = document.querySelector('.log-entry')
        start_log.classList.add('active')
        replace_log(start_log.getAttribute('data-url'))
    }

});
