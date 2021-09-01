
import { select } from 'd3-selection'
import { scaleLinear } from 'd3-scale'
import { line } from 'd3-shape'
import { axisBottom } from 'd3-axis'
import hook from '../lib/hook.js'
import dataUtils from '../lib/data.js'

let drawUntilMinMax = true

const d3 = Object.assign({}, {
  select,
  scaleLinear,
  line,
  axisBottom,
})

let margins = {
  top: 20,
  right: 10,
  bottom: 35,
  left: 10
}

// MATH FUNCTIONSGaussian probability function
function gaussian(x, mean, sigma) {
  const gaussianConstant = 1 / (Math.sqrt(2 * Math.PI) * sigma)
  x = (x - mean) / sigma
  return gaussianConstant * (Math.exp(-0.5 * (x * x)))
}

function clipMinMax(min, max, value) {
  return value < min ? min : value > max ? max : value
}

// DATA FUNCTIONS
function makeGroupDistributionData(params) {
  let p = 0, q = 0, data = []
  let sampleSize = 1000;
  data.push({ 'q': params.minValue, 'p': 0 })
  for (var i = 0; i < (sampleSize); i++) {
    q = (params.min + ((params.max - params.min) / sampleSize * i))
    p = gaussian(q, params.mean, params.sigma)
    data.push({
      'q': clipMinMax(params.minValue, params.maxValue, q),
      'p': p
    })
  }
  data[0].q = data[1].q
  data.push({ 'q': params.maxValue, 'p': data[data.length - 1].p })
  data.push({ 'q': params.maxValue, 'p': 0 })
  return data
}

// DRAW FUNCTIONS
function makeCanvasGroup(element, baseSize) {
  element.innerHTML = ''
  let svg = d3.select(element).append('svg')
    .attr('width', baseSize)
    .attr('height', baseSize / 2)

  let chart = svg.append('g')
    .attr('transform', `translate(${margins.left},${margins.top})`)
  return chart
}

function draw_normal_distribution(chart, params, baseSize) {

  let data = makeGroupDistributionData(params)
  let xScale = d3.scaleLinear()
    .range([0, baseSize - margins.left - margins.right])
    .domain([params.min, params.max])

  let yScale = d3.scaleLinear()
    .range([(baseSize / 2) - margins.top - margins.bottom, 0])
    .domain([0, 1])//gaussian(params.mean, params.mean, params.sigma)])

  let plotLine = d3.line()
    .x(data => xScale(data.q))
    .y(data => yScale(data.p))

  chart.append('path')
    .datum(data)
    .attr('class', 'line')
    .attr('d', plotLine)
    .attr('stroke-width', 1)
    .style('fill', 'hsl(26, 100%, 70%)')
    .style('stroke', 'hsl(26, 100%, 70%)')

  let axis_position_y = yScale(0)
  chart.append('g')
    .attr('class', 'x axis')
    .attr('transform', "translate(0," + axis_position_y + ")")
    .call(d3.axisBottom(xScale))
}

function drawUser(chart, params, baseSize) {

  let xScale = d3.scaleLinear()
    .range([params.min, baseSize - margins.left - margins.right])
    .domain([1, params.max])

  let yScale = d3.scaleLinear()
    .range([(baseSize / 2) - margins.top - margins.bottom, 0])
    .domain([0, 1])

  let data = [{ 'x': params.userScore, 'y': 0 }, { 'x': params.userScore, 'y': 0.8 }]

  let plotLine = d3.line()
    .x(data => xScale(data.x))
    .y(data => yScale(data.y))

  chart.append('path')
    .datum(data)
    .attr('class', 'line')
    .attr('d', plotLine)
    .attr('stroke-width', 6)
    .style('opacity', '0.75')
    .style('stroke', 'hsl(210, 87%, 62%)')
}

// HANDLE REQUEST
let drawNormalDistribution = (element, data, group, options) => {

  let drawableData = dataUtils.getDrawableGroupData(data, group)
  let subscaleData = drawableData[0].subscales[0]
  let groupData = subscaleData.groupResult.data['subscale-key-data']

  let baseSize = element.offsetWidth
  let groupAvgParams = {
    'mean': groupData.average / subscaleData.questionCount,
    'min': groupData.min / subscaleData.questionCount,
    'max': groupData.max / subscaleData.questionCount,
    'minValue': (drawUntilMinMax ? groupData.min : subscaleData.group.data.minValue) / subscaleData.questionCount,
    'maxValue': (drawUntilMinMax ? groupData.max : subscaleData.group.data.maxValue) / subscaleData.questionCount,
    'sigma': groupData['standard-deviation'] / subscaleData.questionCount,
    'userScore': subscaleData.score / subscaleData.questionCount
  }

  let canvasGroup = makeCanvasGroup(element, baseSize)
  draw_normal_distribution(canvasGroup, groupAvgParams, baseSize)
  drawUser(canvasGroup, groupAvgParams, baseSize)
}

let filename = document.currentScript.src.split('/').reverse()[0].replace(/\.js\??.*$/, '')
hook('.' + filename, drawNormalDistribution, { 'hide_buttons': true })

