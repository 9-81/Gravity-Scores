import { select } from 'd3-selection'
import { scaleOrdinal, scaleLinear, scalePoint } from 'd3-scale'
import { range } from 'd3-array'
import { format } from 'd3-format'
import { line } from 'd3-shape'
import tip from 'd3-tip'
import { axisBottom } from 'd3-axis'
import { curveLinearClosed, lineRadial, curveCardinalClosed } from 'd3-shape'

import hook from '../lib/hook.js'
import dataUtils from '../lib/data.js'

let drawUntilMinMax = true


const d3 = Object.assign({}, {
  select,
  scaleLinear,
  scaleOrdinal,
  scalePoint,
  line,
  axisBottom,
  tip,
  range,
  format,
  curveLinearClosed,
  lineRadial,
  curveCardinalClosed,
})


let margins = {
  top: 20,
  right: 50,
  bottom: 50,
  left: 50
}

function computeData(data, baseSize) {

  // PREPARE STUFF

  let computedData = {}

  let maxGroup = data[0].subscales.reduce((subscale1, subscale2) => {
    if (subscale1.groupResult == undefined)
      return subscale2

    if (subscale2.groupResult == undefined)
      return subscale1

    if (subscale1.groupResult.data['subscale-key-data'].max / subscale1.questionCount > subscale2.max / subscale2.questionCount)
      return subscale1
    return subscale2
  })

  let minGroup = data[0].subscales.reduce((subscale1, subscale2) => {
    if (subscale1.groupResult == undefined)
      return subscale2

    if (subscale2.groupResult == undefined)
      return subscale1

    if (subscale1.groupResult.data['subscale-key-data'].max / subscale1.questionCount < subscale2.max / subscale2.questionCount)
      return subscale1
    return subscale2
  })

  // COMPUTE DATA
  computedData.chartMax = maxGroup.groupResult.data['subscale-key-data'].max / maxGroup.questionCount
  computedData.chartMin = minGroup.groupResult.data['subscale-key-data'].min / minGroup.questionCount
  computedData.rScale = d3.scaleLinear()
    .range([0, baseSize / 2 - (margins.top + margins.bottom)])
    .domain([0, computedData.chartMax])

  return computedData

}

function drawRadarAxis(g, data, baseSize) {

  let computedData = computeData(data, baseSize)

  let chartMin = computedData.chartMin
  let chartMax = computedData.chartMax
  let rScale = computedData.rScale

  // DRAW AXIS
  let axisGrid = g.append('g').attr('class', 'axisWrapper')
  axisGrid.selectAll('.levels')
    .data(d3.range(0, chartMax + 1).reverse())
    .enter()
    .append('circle')
    .attr('class', 'gridCircle')
    .attr('r', d => rScale(1) * d)
    .style('stroke', '#5b5959')
    .style('fill-opacity', 0)


  axisGrid.selectAll('.axisLabel')
    .data(d3.range(chartMin, chartMax + 1).reverse())
    .enter().append('text')
    .attr('class', 'axisLabel')
    .attr('x', rScale(1 / 3))
    .attr('y', d => - (rScale(1) * d) - rScale(1 / chartMax))
    .attr('dy', '0.4em')
    .style('font-size', baseSize / 40)
    .attr('fill', 'black')
    .text(d => d)

  let axis = axisGrid.selectAll('.axis')
    .data(data[0].subscales)
    .enter()
    .append('g')
    .attr('class', 'axis')

  // Append the lines
  axis.append('line')
    .attr('x1', 0)
    .attr('y1', 0)
    .attr('x2', (d, i) => rScale(chartMax * 1.05) * Math.cos((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    .attr('y2', (d, i) => rScale(chartMax * 1.05) * Math.sin((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    .attr('class', 'line')
    .style('stroke', '#504d4d')
    .style('stroke-width', '2px')
}

function drawRadarGroupArea(g, data, baseSize, groupName) {

  let computedData = computeData(data, baseSize)

  let chartMin = computedData.chartMin
  let chartMax = computedData.chartMax
  let rScale = computedData.rScale
  let color = groupName === 'user' ? 'hsl(210, 87%, 60%)' : 'hsl(26, 100%, 65%)'

  let radarLine = d3.lineRadial()
    .curve(d3.curveLinearClosed)
    .radius((d, i) => {
      if (groupName !== 'user' && d.groupResult == undefined)
        return rScale(chartMin)
      return rScale((groupName === 'user') ? d.score / d.questionCount : d.groupResult.data['subscale-key-data'].average / d.questionCount)
    })
    .angle((d, i) => i * ((2 * Math.PI) / data[0].subscales.length))

  g.append('g')
    .append('path')
    .datum(data[0].subscales)
    .attr('d', radarLine)
    .attr('class', 'line')
    .attr('stroke-width', 1)
    .style('fill', color)
    .style('opacity', '0.6')
    .style('stroke', color)
    .style("stroke-opacity", 1)
}

function drawRadarGroupPoints(g, data, baseSize, group) {
  let computedData = computeData(data, baseSize)
  let rScale = computedData.rScale
  let chartMin = computedData.chartMin
  let chartMax = computedData.chartMax
  let color = (group === 'user') ? 'hsl(210, 87%, 60%)' : 'hsl(26, 100%, 65%)'

  let scoreCircleWrapper = g.selectAll('.scoreCircleWrapper')
    .data([data[0].subscales])
    .enter().append('g')
    .attr('class', 'radarCircleWrapper')
    .style('stroke-width', 1)
    .style('stroke', color)

  scoreCircleWrapper.selectAll('.radarScoreCircle')
    .data(d => d)
    .enter().append('circle')
    .attr('class', 'scoreCircle')
    .attr('r', rScale(0.17))
    .attr('cx', (d, i) => {
      if (group !== 'user' && d.groupResult == undefined)
        return rScale(chartMin * Math.cos((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
      return rScale((((group === 'user') ? d.score : d.groupResult.data['subscale-key-data'].average) / d.questionCount) * Math.cos((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    })
    .attr('cy', (d, i) => {
      if (group !== 'user' && d.groupResult == undefined)
        return rScale(chartMin * Math.sin((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
      return rScale((((group === 'user') ? d.score : d.groupResult.data['subscale-key-data'].average) / d.questionCount) * Math.sin((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    })
    .style('fill', color)

}


function hookRadarTooltips(g, data, baseSize, group) {
  let computedData = computeData(data, baseSize)
  let rScale = computedData.rScale
  let chartMin = computedData.chartMin

  let scoreCircleWrapper = g.selectAll('.tooltipCircleWrapper')
    .data([data[0].subscales])
    .enter().append('g')
    .attr('class', 'tooltip')
    .style('stroke-width', 1)
    .style('stroke', 'black')

  let tip = d3.tip()
    .attr('class', 'd3-tip')
    .offset([-5 - baseSize / 50, 0])
    .html(d => {
      if (group !== 'user' && d.groupResult == undefined)
        return chartMin
      return (Math.round(100 * ((group === 'user') ? d.score : d.groupResult.data['subscale-key-data'].average) / d.questionCount)) / 100
    })
  g.call(tip)

  scoreCircleWrapper.selectAll('.radarScoreCircle')
    .data(d => d)
    .enter().append('circle')
    .attr('class', 'tooltipHookCircle')
    .attr('r', rScale(0.17))
    .attr('cx', (d, i) => {
      if (group !== 'user' && d.groupResult == undefined)
        return rScale(chartMin * Math.cos((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
      return rScale((((group === 'user') ? d.score : d.groupResult.data['subscale-key-data'].average) / d.questionCount) * Math.cos((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    })
    .attr('cy', (d, i) => {
      if (group !== 'user' && d.groupResult == undefined)
        return rScale(chartMin * Math.sin((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
      return rScale((((group === 'user') ? d.score : d.groupResult.data['subscale-key-data'].average) / d.questionCount) * Math.sin((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    })
    .style('opacity', 0.1)
    .style('stroke-opacity', '1.0')
    .on('mouseover', tip.show)
    .on('mouseout', tip.hide)

}

function drawLabels(g, data, baseSize) {
  let computedData = computeData(data, baseSize)
  let chartMin = computedData.chartMin
  let chartMax = computedData.chartMax
  let rScale = computedData.rScale

  let legend = g.append('g')
    .attr('class', 'legend')
    .attr('height', 300)
    .attr('width', 500)

  // add legend texts
  legend.selectAll('text')
    .data(data[0].subscales)
    .enter()
    .append('text')
    .attr('x', (d, i) => rScale(chartMax * 1.17) * Math.cos((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2))
    .attr('y', function (d, i) {
      return rScale(chartMax * 1.17) * Math.sin((Math.PI * 2 / data[0].subscales.length) * i - Math.PI / 2)
    })
    .text(d => d.name)
    .style('font-size', baseSize / 40)
    .style("text-anchor", "middle")
}

function drawRadarChart(element, data, group, options) {
  console.log(data)
  let baseSize = element.offsetWidth / 1.1 // Reduce size, in favor of the chart overflows
  let drawableData = dataUtils.getDrawableGroupData(data, group)


  let svg = d3.select(element).append('svg')
    .attr('width', baseSize)
    .attr('height', baseSize)
    .style('overflow', 'visible')

  let g = svg.append('g')
    .attr('transform', 'translate(' + (baseSize / 2 + margins.left) + ',' + (baseSize / 2 + margins.top) + ')')

  drawRadarAxis(g, drawableData, baseSize)
  drawLabels(g, drawableData, baseSize)
  drawRadarGroupArea(g, drawableData, baseSize, group)
  drawRadarGroupPoints(g, drawableData, baseSize, group)
  drawRadarGroupArea(g, drawableData, baseSize, 'user')
  drawRadarGroupPoints(g, drawableData, baseSize, 'user')
  hookRadarTooltips(g, drawableData, baseSize, group)
  hookRadarTooltips(g, drawableData, baseSize, 'user')

}

let filename = document.currentScript.src.split('/').reverse()[0].replace(/\.js\??.*$/, '')
hook('.' + filename, drawRadarChart, { 'hide_buttons': true })
