import Axios from 'axios'

async function getEvaluationData(evaluationId) {

    let requestURL = 'gravityscores/v1/evaluation/' + evaluationId + '?option=scored'

    let api = Axios.create({
        baseURL: localURLs.rest,
        headers: {
            'content-type': 'application/json',
            'X-WP-Nonce': nonce
        }
    })

    let response = await api.get(requestURL)

    return response.data

}

function drawGroupButtons(area, selector, drawChartCallable, data, options) {

    let elements = document.querySelectorAll('.gs-buttons  ' + selector)

    let groups = data.tests.map(test => {
        return test.subscales.map(subscale => {
            return subscale.group_results.group
        })
    }).filter((x, i, a) => a.indexOf(x) == i)

    groups.forEach(group => {
        let groupButton = document.createElement("button")
        groupButton.classList.add('spitze-gs-group-button')
        groupButton.innerText = group

        groupButton.addEventListener("click", function () {

            Array.prototype.forEach.call(area.children, (button) => {
                button.classList.remove('active')
            })
            this.classList.add('active');

            charts = document.querySelectorAll('.gs-chart ' + selector)

            charts.forEach((chart) => {
                drawChartCallable(chart, data, group, options)
            })

        }, false)

    })

}

async function hook(selector, drawChartCallable, options, mkButtonsCallable = drawGroupButtons) {

    let elements = document.querySelectorAll(selector)

    // Unique Evaluation Ids
    let evaluationIds = Array.from(elements).map(element => {

        return element.getAttribute('data-id')

    }).filter((value, index, self) => {

        return self.indexOf(value) === index;

    })

    // Get Data
    let dataPromises = evaluationIds.map(getEvaluationData)

    // Draw Buttons and Charts
    dataPromises.forEach(async promise => promise.then(data => {
        elements.forEach(element => {

            if (data == '') {
                console.log('Rest response is empty.')
                return
            }

            if (element.getAttribute('data-id') != data.evaluations[0].id)
                return

            if (element.classList.contains('gs-chart')) {

                if (data.tests[0].subscales[0].group_results[0] !== undefined) {
                    drawChartCallable(element, data, data.tests[0].subscales[0].group_results[0].group, options)
                } else {
                    element.innerText = 'You need to log in, to see the chart.'
                    return
                }

            } else if (element.classList.contains('gs-buttons')) {

                mkButtonsCallable(element, selector, drawChartCallable, data, options)

            }

        })

    }))

}

export default hook
