
document.addEventListener('DOMContentLoaded', () => {

    let nonce = document.querySelector('#nonce').getAttribute('data-nonce')


    let api = axios.create({
        baseURL: 'http://192.168.13.37/wordpress/index.php/wp-json/',
        headers: {
            'content-type': 'application/json',
            'X-WP-Nonce': nonce
        }
    })

    api.get('gravityscores/v1/test/1?option=scored').then((response) => {
        console.log(response['data'])
    });
})
