<template>
  <transition name="fade">
    <ul :class="[last?'last':null]">
      <li>
        <div class="li-wrapper" :class="[hasChildren?'':'childless']">
          <span v-if="hasChildren" @click="toggleShow(node.label)">
            <i class="li-indicator fa fa-minus" v-if="node.open"></i>
            <i class="li-indicator fa fa-plus" v-else></i>
          </span>
          <template v-if="editMode">
            <div v-if="(node.mode&2)!==0" class="li-input-wrapper">
              <input class="li-input"
                     type="text"
                     :maxlength="maxlength"
                     v-model="newValue" @focus="$event.target.select()"
                     @keyup.enter="updateItem" autocomplete="false"
                     @keyup.escape="cancelItem"
                     placeholder="Category name" v-focus/>
              <div class="li-btn-group" :class="[isUpdating?'updating':null]">
                <template v-if="!isUpdating">
                  <button class="btn btn-sm" type="button" :disabled="!newValue"
                          :aria-disabled="!newValue"
                          title="confirm" @click="updateItem">
                    <i class="fa fa-check"></i>
                  </button>
                  <button class="btn btn-sm" type="button" title="cancel" @click="cancelItem">
                    <i class="fa fa-ban"></i>
                  </button>
                </template>
                <template v-else>
                  <i class="fa fa-refresh fa-pulse"></i>
                </template>
              </div>
            </div>
            <div v-else class="li-btn-group-wrapper"
                 :class="{'tree-searched':node.mode===5}"
                 @dblclick="toggleShow(node.label)">
              <span class="li-label">{{node.label}}</span>
              <div class="li-btn-group">
                <button type="button" class="btn btn-sm btn-default" @click="addItem"
                        :title="$t('pages.blog_categories.add_child_node',{name:node.label})">
                  <i class="fa fa-plus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-default" @click="editItem"
                        :title="$t('pages.blog_categories.edit_node',{name:node.label})">
                  <i class="fa fa-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-default" @click="deleteItem"
                        :title="$t('pages.blog_categories.delete_node',{name:node.label})">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox"
                     v-model="isChecked">
              <label class="li-label form-check-label" :class="{'li-label-searched':node.mode===5}"
                     @dblclick="toggleShow(node.label)">{{node.label}}</label>
            </div>
          </template>
        </div>
        <div v-if="node.open" :key="'p'+node.label+idx"
            v-for="(child,idx) of node.children">
          <tree-list-item :node="child" @event="emits" @category-selected="categorySelected"
                          :edit-mode="editMode" :last="(idx===node.children.length-1)"
                          :key="'c'+node.label+idx"></tree-list-item>
        </div>
      </li>
    </ul>
  </transition>
</template>
<script>
  export default {
    name: 'tree-list-item',
    props: {
      node: {required: true},
      editMode: {
        type: Boolean,
        default: () => true
      },
      maxlength: {
        type: Number,
        default: () => 75
      },
      last: {
        type: Boolean
      }
    },
    data: function () {
      return {
        newValue: null,
        isUpdating: false
      }
    },
    computed: {
      hasChildren () {
        return this.node.children.length > 0
      },
      isChecked: {
        get () {
          return this.node.selected
        },
        set (value) {
          this.node.selected = value
        }
      }
    },
    watch: {
      isChecked (value) {
        this.categorySelected(
          this.node.id,
          (value) ? 'add' : 'del'
        )
      }
    },
    directives: {
      focus: {
        inserted: function (el) {
          el.focus()
        }
      }
    },
    updated () {
      if (this.node.mode === 1) {
        this.isUpdating = false
      }
    },
    mounted () {
      //Making sure the html input element has an initial value
      this.newValue = this.node.label
    },
    methods: {
      emits (nodeMap, data) {
        this.$emit('event', [this.node.id].concat(nodeMap), data)
      },
      categorySelected (value, mode) {
        this.$emit('category-selected', value, mode)
      },
      addItem () {
        this.emits([], this.makeDataObject('add'))
      },
      editItem () {
        this.emits([], this.makeDataObject('edit'))
      },
      deleteItem () {
        this.emits([], this.makeDataObject('delete'))
      },
      updateItem () {
        if (this.newValue.length) {
          this.isUpdating = true
          this.emits([],
            {method: 'update', newValue: this.newValue, target: {id: this.node.id, label: this.node.label}})
        } else {
          this.cancelItem()
        }
      },
      cancelItem () {
        // this.newValue = this.node.label
        switch (this.node.mode) {
          case 6:
            this.emits([], this.makeDataObject('cancelCreation'))
            break
          default:
            this.emits([], this.makeDataObject('cancel'))
        }
      },
      toggleShow () {
        this.emits([], this.makeDataObject('toggleShow'))
      },
      makeDataObject (method) {
        return {method: method, target: {id: this.node.id, label: this.node.label}}
      }
    }
  }
</script>