import Vue from 'vuejs'
import Axios from 'axios'

document.addEventListener('DOMContentLoaded', function() {

    window.app = new Vue({
        el: '.wrap.evaluation',
        data: {
            wizardStep: 0,
            wizardEnabled: 0,
            api: Axios.create({
                baseURL: localURLs.rest,
                headers: {
                    'content-type': 'application/json',
                    'X-WP-Nonce': nonce
                },
            }),
            evaluationName: '',
            visualizations: [],
            tests: [],
            selectedVisualization: undefined,
            previousVisualization: undefined,
            selectedTests: []
        },
        computed: {

        },
        beforeMount() {
            this.api.get('gravityscores/v1/visualizations').then((response) => {
                Vue.set(this, 'visualizations', response.data.visualizations)
            });

            this.api.get('gravityscores/v1/tests').then((response) => {
                Vue.set(this, 'tests', response.data.tests)
            });

        },
        methods: {
            wizardSelectStep: function(step) {
                this.wizardStep = (this.wizardEnabled >= step) ? step : this.wizardStep
                Vue.set(this, 'selectedVisualization', this.previousVisualization)
            },
            wizardNextStep: function() {

                if (this.wizardStep == 0) {
                    this.wizardEnabled = 1;
                }

                if (this.wizardStep == 1) {

                    if (this.wizardEnabled == 1) {
                        this.wizardEnabled = 2
                    } else if (this.previousVisualization !== this.selectedVisualization) {
                        Vue.set(this, 'previousVisualization', this.selectedVisualization)
                        Vue.set(this, 'selectedTests', [])
                        this.wizardEnabled = 2
                    }

                }

                if (this.wizardStep == 2) {

                    if (this.wizardEnabled == 2) {
                        this.wizardEnabled = 3
                        this.tests.forEach(test => {
                            this.api.get('gravityscores/v1/test/' + test.id).then((response) => {
                                Vue.set(test, 'subscales', response.data.tests[0].subscales)
                            });

                        })
                    }
                }


                if (this.wizardStep == 3) {
                    this.wizardEnabled = 4
                }

                this.wizardStep += 1
            },
            selectVisualization: function(visualization) {

                if (this.selectedVisualization === undefined) {
                    Vue.set(this, 'previousVisualization', visualization)
                }
                Vue.set(this, 'selectedVisualization', visualization)
            },
            toggleTest: function(test) {
                this.selectedTests.includes(test) ? Vue.delete(this.selectedTests, this.selectedTests.indexOf(test)) : this.selectedTests.push(test)
            },
            submit: function() {

                let subscale_ids = []

                this.tests.forEach(test => {
                    test.subscales.forEach(subscale => {
                        if (subscale.added) {
                            subscale_ids.push(subscale.id)
                        }
                    })
                })

                let data = {
                    visualization: this.selectedVisualization,
                    tests: this.tests,
                    evaluations: [{
                        title: this.evaluationName,
                        subscale_ids: subscale_ids,
                        visualization_id: this.selectedVisualization.id
                    }],
                    options: {
                        import_evaluations_only: true
                    }
                }

                this.api.post('gravityscores/v1/import/', data).then((response) => {
                    this.importStatus = (this.importStatus === null) ? response.data : this.importStatus && response.data
                    window.location = localURLs.home + '/wp-admin/admin.php?page=gravityscores'
                });

            }
        }
    })

})