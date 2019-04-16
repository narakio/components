import axios from 'axios'
import Errors from './Errors'
import { deepCopy, hasFile, toFormData } from './util'

class Form {
  /**
   * Create a new form instance.
   *
   * @param {Object} data
   * @param {Boolean} trackChanges
   */
  constructor (data = null, trackChanges = false) {
    this.busy = false
    this.successful = false
    this.errors = new Errors()
    this.fields = {}
    this.changedFields = {}
    this.trackChanges = trackChanges
    if (data != null) {
      this.originalData = deepCopy(data)
      Object.assign(this.fields, data)
    }
  }

  /**
   * Fill the form fields with passed data
   *
   * @param data
   */
  fill (data) {
    Object.keys(data).forEach(key => {
      this.fields[key] = data[key]
    })
  }

  /**
   * Add a new field to the form
   * @param {String} name
   * @param {String} value
   */
  addField (name, value) {
    this.fields[name] = value
  }

  /**
   * Get the value of a particular field
   *
   * @param {String} name
   * @returns {*}
   */
  getField (name) {
    if (this.fields.hasOwnProperty(name)) {
      return this.fields[name]
    }
    return null
  }

  /**
   * Get all available fields in the form
   *
   * @returns {{}}
   */
  getFormData () {
    return this.fields
  }

  /**
   * Add a field in the form that has been modified by the user
   * (a field marked for save)
   * @param field
   */
  addChangedField (field) {
    if (!this.changedFields.hasOwnProperty(field)) {
      this.changedFields[field] = true
    }
  }

  /**
   * Resets the object that holds fields that have received user input
   */
  resetChangedFields () {
    this.changedFields = {}
  }

  /**
   *
   * @returns {{}|*}
   */
  getChangedFields () {
    return this.changedFields
  }

  /**
   *
   * @param field
   * @returns {boolean}
   */
  hasFieldChanged (field) {
    return this.changedFields.hasOwnProperty(field)
  }

  /**
   * Has there been any user interaction with the form that changed it?
   *
   * @returns {boolean}
   */
  hasDetectedChanges () {
    return (Object.keys(this.changedFields).length > 0)
  }

  /**
   * Set the trackChanges booolean for when we want to save the form
   * only when fields changed
   *
   * @param value
   */
  setTrackChanges (value) {
    this.trackChanges = value
  }

  /**
   * Get the form data.
   *
   * @return {Object}
   */
  data () {
    const data = {}
    if (this.trackChanges) {
      Object.keys(this.fields).forEach(key => {
        if (this.changedFields.hasOwnProperty(key)) {
          data[key] = this.fields[key]
        }
      })
    } else {
      Object.keys(this.fields).forEach(key => {
        data[key] = this.fields[key]
      })
    }
    return data
  }

  /**
   * Get the form data keys.
   *
   * @return {Array}
   */
  keys () {
    return Object.keys(this.fields)
  }

  /**
   * Start processing the form.
   */
  startProcessing () {
    this.errors.clear()
    this.busy = true
    this.successful = false
  }

  /**
   * Finish processing the form.
   */
  finishProcessing () {
    this.busy = false
    this.successful = true
    this.resetChangedFields()
  }

  /**
   * Clear the form errors.
   */
  clear () {
    this.errors.clear()
    this.successful = false
  }

  /**
   * Reset the form fields.
   */
  reset () {
    Object.keys(this.fields)
      .forEach(key => {
        this.fields[key] = deepCopy(this.originalData[key])
      })
    this.changedFields = {}
  }

  /**
   * Submit the from via a GET request.
   *
   * @param  {String} url
   * @return {Promise}
   */
  get (url) {
    return this.submit('get', url)
  }

  /**
   * Submit the from via a POST request.
   *
   * @param  {String} url
   * @return {Promise}
   */
  post (url) {
    return this.submit('post', url)
  }

  /**
   * Submit the from via a PATCH request.
   *
   * @param  {String} url
   * @return {Promise}
   */
  patch (url) {
    return this.submit('patch', url)
  }

  /**
   * Submit the from via a PUT request.
   *
   * @param  {String} url
   * @return {Promise}
   */
  put (url) {
    return this.submit('put', url)
  }

  /**
   * Submit the from via a DELETE request.
   *
   * @param  {String} url
   * @return {Promise}
   */
  delete (url) {
    return this.submit('delete', url)
  }

  /**
   * Submit the form data via an HTTP request.
   *
   * @param  {String} method (get, post, patch, put)
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  submit (method, url, config = {}) {
    this.startProcessing()

    url = this.route(url)
    let data = this.data()

    if (hasFile(data)) {
      data = toFormData(data)
    }

    if (method === 'get') {
      data = {params: data}
    }

    return new Promise((resolve, reject) => {
      axios.request({url, method, data, ...config})
        .then(response => {
          this.finishProcessing()

          resolve(response)
        })
        .catch(error => {
          this.busy = false

          if (error.response) {
            this.errors.set(this.extractErrors(error.response))
          }

          reject(error)
        })
    })
  }

  /**
   * Extract the errors from the response object.
   *
   * @param  {Object} response
   * @return {Object}
   */
  extractErrors (response) {
    if (!response.data || typeof response.data !== 'object') {
      return null
    }

    if (response.data.errors) {
      return {...response.data.errors}
    }

    if (response.data.alertMessage) {
      return {error: response.data.alertMessage}
    }

    return {...response.data}
  }

  /**
   * Get a named route.
   *
   * @param  {String} name
   * @return {Object} parameters
   * @return {String}
   */
  route (name, parameters = {}) {
    let url = name

    if (Form.routes.hasOwnProperty(name)) {
      url = decodeURI(Form.routes[name])
    }

    if (typeof parameters !== 'object') {
      parameters = {id: parameters}
    }

    Object.keys(parameters).forEach(key => {
      url = url.replace(`{${key}}`, parameters[key])
    })
    return url
  }

  /**
   * Clear errors on keydown.
   *
   * @param {KeyboardEvent} event
   */
  onKeydown (event) {
    if (event.target.name) {
      this.errors.clear(event.target.name)
    }
  }
}

Form.routes = {}
Form.ignore = [
  'busy',
  'successful',
  'errors',
  'originalData',
  'changedFields',
  'trackChanges']

export default Form
