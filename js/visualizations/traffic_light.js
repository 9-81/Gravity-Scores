import hook from '../lib/hook.js'
import dataUtils from '../lib/data.js'

function draw_traffic_light(element, data, group, options) {

  let drawableData = dataUtils.getDrawableGroupData(data, group)
  let chartData = {
    userScore: drawableData[0].subscales[0].score,
    group: drawableData[0].subscales[0].groupResult.data['subscale-key-data']
  }

  let traffic_light = document.createElement('div')
  traffic_light.classList.add('traffic_light__hull')

  let active_conditions = {
    'red_bulb': chartData.userScore < chartData.group.average - chartData.group['standard-deviation'],
    'yellow_bulb': chartData.userScore > chartData.group.average - chartData.group['standard-deviation'] && chartData.userScore < chartData.group.average + chartData.group['standard-deviation'],
    'green_bulb': chartData.userScore > chartData.group.average + chartData.group['standard-deviation'],
  }

  let light_bulbs = {
    'red_bulb': document.createElement('div'),
    'yellow_bulb': document.createElement('div'),
    'green_bulb': document.createElement('div')
  }

  Object.keys(light_bulbs).forEach(bulbName => {
    light_bulbs[bulbName].classList.add(bulbName)
    light_bulbs[bulbName].classList.add(active_conditions[bulbName] ? 'traffic_light__bulb--active' : 'traffic_light__bulb')
    traffic_light.appendChild(light_bulbs[bulbName])
  })

  element.appendChild(traffic_light)

}

let filename = document.currentScript.src.split('/').reverse()[0].replace(/\.js\??.*$/, '')
hook('.' + filename, draw_traffic_light, { 'hide_buttons': true })
