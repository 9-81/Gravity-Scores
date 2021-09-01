import Vue from 'vuejs'
import Axios from 'axios'
import ListTable from 'vue-wp-list-table';
//import 'vue-wp-list-table/dist/vue-wp-list-table.css';

document.addEventListener('DOMContentLoaded', function() {

    // Add Test Overlay ausblenden
    document.querySelector('#wpwrap').addEventListener('click', function(event) {

        let not_conditions = [
            Array.from(event.target.classList).includes('selectTestOverlay'),
            Array.from(document.querySelectorAll('.selectTestOverlay *')).includes(event.target),
            Array.from(event.target.classList).includes('testListButtonAdd')
        ]

        if (!not_conditions.some(x => x)) {
            window.app.testSelectorActive = false
        }

    })


    window.app = new Vue({
        el: '.wrap.evaluations',
        components: {
            ListTable
        },
        data: {
            api: Axios.create({
                baseURL: localURLs.rest,
                headers: {
                    'content-type': 'application/json',
                    'X-WP-Nonce': nonce
                },
            }),
            document: document,
            console: console,
            evaluations: [],
            visualizations: [],
            edit: undefined,
            editTests: [],
            tests: [],
            currentTitle: '',
            editMode: false,
            editSection: 0,
            selectedVisualization: '',
            testSelectorActive: false,
            searchTerm: '',
            saved: false
        },
        beforeMount() {

            Axios.all([
                this.api.get('gravityscores/v1/evaluations'),
                this.api.get('gravityscores/v1/visualizations'),
                this.api.get('gravityscores/v1/tests')
            ]).then(Axios.spread((response1, response2, response3) => {

                if (Object.keys(response1.data).includes('evaluations')) {
                    Vue.set(this, 'evaluations', response1.data.evaluations)
                } else {
                    Vue.set(this, 'evaluations', [])
                }

                if (Object.keys(response2.data).includes('visualizations')) {
                    Vue.set(this, 'visualizations', response2.data.visualizations)
                } else {
                    Vue.set(this, 'visualizations', [])
                }

                if (Object.keys(response3.data).includes('tests')) {
                    Vue.set(this, 'tests', response3.data.tests)
                } else {
                    Vue.set(this, 'tests', [])
                }

                this.evaluations.forEach(evaluation => {

                    let matching_visualizations = this.visualizations.filter(visualization => {
                        return evaluation.visualization_id == visualization.id
                    })

                    if (matching_visualizations.length > 0) {
                        evaluation.visualization = matching_visualizations[0].name
                    } else {
                        evaluation.visualization = 'not avalable'
                    }
                })

            }))


        },
        computed: {
            filteredTests: function() {
                if (this.searchTerm == '') {
                    return this.tests.filter(test => {
                        return !this.editTests.map(etest => etest.id).includes(test.id)
                    })
                }

                return this.tests.filter(test => {
                    let search_chars = this.searchTerm.toLowerCase().trim().split('')
                    let chars = (test.form_title + ' ' + test.id + ' ' + test.form_id).toLowerCase().split('')

                    for (let index in search_chars) {
                        if (!chars.includes(search_chars[index])) {
                            return false;
                        }
                        chars.splice(chars.indexOf(search_chars[index]), 1);
                    }

                    return !this.editTests.map(etest => etest.id).includes(test.id)
                })
            }
        },
        methods: {
            log: function(logMessage) {
                console.log(logMessage)
                return logMessage
            },
            all_evaluations_table_actions: function(action, evaluation) {

                if (action == 'shortcode') {
                    navigator.clipboard.writeText('[gravityscores id=' + evaluation.id + ']').then(() => {
                        jQuery('.copy-tooltip.success').show(200)
                        setTimeout(() => {
                            jQuery('.copy-tooltip.success').hide(200)
                        }, 2000)

                    }).catch(() => {
                        jQuery('.copy-tooltip.error').show(200)
                        setTimeout(() => {
                            jQuery('.copy-tooltip.error').hide(200)
                        }, 2000)
                    })
                }


                if (action == 'edit') {

                    if (this.edit === undefined || this.edit.id == evaluation.id || window.confirm('You are already editing "' + this.edit.title + '. Do you want to continue without saving?')) {

                        if (this.edit === undefined || this.edit.id != evaluation.id) {
                            this.currentTitle = evaluation.title
                            this.editSection = 0;
                            this.selectedVisualization = this.visualizations.filter(visualization => {
                                return evaluation.visualization_id == visualization.id
                            })[0].id
                        }

                        this.editMode = true;
                        this.edit = evaluation
                        this.saved = false

                        this.api.get('gravityscores/v1/evaluation/' + evaluation.id).then((response) => {
                            this.edit = response.data.evaluations[0]
                            this.editTests = response.data.tests
                        })

                    }

                }

                if (action == 'delete') {

                    if (this.edit != undefined && evaluation.id === this.edit.id) {
                        this.edit = undefined
                        this.editMode = false
                        this.selectedVisualization = ''
                        this.currentTitle = ''
                    }

                    this.api.delete('gravityscores/v1/evaluation/' + evaluation.id).then((response) => {
                        this.evaluations = this.evaluations.filter(element => {
                            return element.id != evaluation.id
                        })

                    }).catch((error) => {
                        console.log(error)
                    })


                }

            },
            onBulkAction: function(action, evaluation_ids) {

                let evaluations = evaluation_ids.map(evaluation_id => {
                    return this.evaluations.filter(evaluation => {
                        return evaluation.id == evaluation_id
                    })[0]
                })

                evaluations.forEach(evaluation => {
                    this.all_evaluations_table_actions(action, evaluation)
                })

            },
            renameEvaluation: function() {
                Vue.set(this.edit, 'title', this.currentTitle)
                this.evaluations.forEach(evaluation => {

                    if (evaluation.id == this.edit.id) {
                        Vue.set(evaluation, 'title', this.currentTitle)
                    }
                })

                let data = {
                    evaluations: [this.edit],
                    options: {
                        import_evaluations_only: true,
                        update: true
                    }
                }


                this.api.put('gravityscores/v1/import/', data).then((response) => {
                    this.saved = true
                    this.editSection = 0
                    //window.location = localURLs.home + '/wp-admin/admin.php?page=gravityscores'
                });

            },
            changeVisualization: function() {

                this.edit.visualization_id = this.selectedVisualization
                this.edit.visualization = this.visualizations.filter(visualization => {
                    return visualization.id == this.edit.visualization_id
                })[0].name

                let data = {
                    evaluations: [this.edit],
                    options: {
                        import_evaluations_only: true,
                        update: true
                    }
                }


                this.api.put('gravityscores/v1/import/', data).then((response) => {
                    this.saved = true
                    this.editSection = 0
                    //this.importStatus = (this.importStatus === null) ? response.data : this.importStatus && response.data
                    //window.location = localURLs.home + '/wp-admin/admin.php?page=gravityscores'
                });
            },
            addTest: function(test) {

                this.api.get('gravityscores/v1/test/' + test.id).then((response) => {
                    this.editTests.push(response.data.tests[0])
                })
            },
            toggleSubscale: function(subscale) {

                if (this.edit.subscale_ids.includes(subscale.id)) {
                    this.edit.subscale_ids = this.edit.subscale_ids.filter(subscale_id => {
                        return subscale_id != subscale.id
                    })
                } else {
                    this.edit.subscale_ids.push(subscale.id)
                }

            },
            saveSubscales: function() {

                let data = {
                    evaluations: [this.edit],
                    options: {
                        import_evaluations_only: true,
                        update: true
                    }
                }


                this.api.put('gravityscores/v1/import/', data).then((response) => {
                    this.editSection = 0
                    this.saved = true
                    //this.importStatus = (this.importStatus === null) ? response.data : this.importStatus && response.data
                    //window.location = localURLs.home + '/wp-admin/admin.php?page=gravityscores'
                });

            }
        }
    })

})