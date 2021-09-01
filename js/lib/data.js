function getDrawableData(data) {
    let drawableData = []
    let tempEvaluationData = {};

    data.evaluations.forEach(evaluation => {

        // Link subscales to evaluations
        evaluation.subscales = []
        data.tests.forEach((test) => {
            test.subscales.forEach(subscale => {
                if (evaluation.subscale_ids.includes(subscale.id)) {
                    evaluation.subscales.push(subscale)
                }
            })
        })

        // Collect data
        tempEvaluationData.subscales = []
        tempEvaluationData.title = evaluation.title
        evaluation.subscales.forEach(subscale => {
            tempEvaluationData.subscales.push({
                name: subscale.name,
                score: subscale.score,
                groupResults: subscale.group_results,
                questionCount: subscale.evaluables.length
            })
        })

        drawableData.push(tempEvaluationData)
    });

    return drawableData;
}

function getDrawableGroupData(data, group) {

    let processedData = getDrawableData(data)

    processedData.forEach(evaluation => {

        evaluation.subscales.forEach(subscale => {
            subscale.groupResult = subscale.groupResults.filter(result => {
                return result.group == group
            })[0]
            delete subscale.groupResults
        })

    })

    return processedData
}

function getGroupNames(data) {
    // ToDo: Implement
    return data
}

export default { getGroupNames, getDrawableData, getDrawableGroupData }