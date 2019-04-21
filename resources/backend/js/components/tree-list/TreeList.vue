<template>
  <div class="container" :class="[mini?'tree-list-mini':'tree-list']">
    <div class="row mb-3">
      <div class="col-lg">
        <search :terms="searchTerms" @show="searchEvent"/>
      </div>
    </div>
    <div class="row p-0 m-0">
      <button v-if="editMode" type="button"
              class="btn btn-primary"
              @click="addRoot">{{addRootButtonLabel}}
      </button>
      <button type="button"
              class="btn btn-primary"
              @click="toggleExpand">{{treeExpanded?$t('general.expand_all'):$t('general.collapse_all')}}
      </button>
    </div>
    <div class="row p-0 mt-3 ml-1 tree-container">
      <tree-list-item v-for="(node,idx) in treeData"
                      :key="node.label+idx"
                      :node="node"
                      @event="handleEvent"
                      @tree-selected="treeSelected"
                      :edit-mode="editMode" :last="idx===treeData.length-1">
      </tree-list-item>
    </div>
  </div>
</template>
<script>
  import TreeListItem from 'back_path/components/tree-list/TreeListItem'
  import Search from 'back_path/components/tree-list/Search'

  export default {
    name: 'tree-list',
    components: {
      TreeListItem,
      Search
    },
    props: {
      data: {required: true},
      editMode: {default: true},
      addRootButtonLabel: {required: true},
      addCallback: {require: true, type: Function},
      editCallback: {require: true, type: Function},
      deleteCallback: {require: true, type: Function},
      mini: {default: () => false},
      multiSelect: {default: () => false}
    },
    data () {
      return {
        treeData: [],
        searchInput: null,
        searchTerms: [],
        buffer: [],
        treeExpanded: false,
        selectedNodes: []
      }
    },
    watch: {
      data (incoming) {
        this.treeData = incoming
      },
      treeData () {
        this.searchTerms = []
        for (let i in this.treeData) {
          this.searchTerms = this.searchTerms.concat(this.buildSearchTerms(this.treeData[i]))
        }
      }
    },
    methods: {
      treeSelected (val, mode) {
        if (mode === 'add') {
          if (this.selectedNodes.indexOf(val) === -1) {
            if (this.multiSelect) {
              this.selectedNodes.push(val)
            } else {
              this.selectedNodes = [val]
            }
          }
        } else {
          let i = this.selectedNodes.indexOf(val)
          if (i > -1) {
            if (this.multiSelect) {
              this.selectedNodes.splice(i, 1)
            } else {
              this.selectedNodes = []
            }
          }
        }
        this.$emit('tree-selected', this.selectedNodes)
      },
      async toggleExpand () {
        this.treeExpanded = !this.treeExpanded
        for (let node of this.searchTerms) {
          node.data.method = this.treeExpanded ? 'close' : 'open'
          await this.handleEvent(node.nodeMap, node.data)
        }
      },
      buildSearchTerms (node) {
        let result = [{data: {target: {id: node.id, label: node.label}}, nodeMap: []}]
        for (let subNodes of node.children) {
          result = result.concat(this.buildSearchTerms(subNodes))
        }

        for (let term of result) {
          term.nodeMap = [node.id].concat(term.nodeMap)
          term.data.method = 'show'
        }
        return result
      },
      addRoot () {
        this.treeData.push({label: '', id: null, open: true, selected: false, mode: 6, children: [], parent: ''})
      },
      async handleEvent (nodeMap, payload) {
        await this.checkBuffer()
        let td = []
        for (let node of this.treeData) {
          let n = await this.handleThis(nodeMap, payload, node)
          if (n) {
            td.push(n)
          }
        }
        this.treeData = td
      },
      //Called in case we need to cancel out some action we did just before
      //like remove search highlighting
      async checkBuffer () {
        if (this.buffer.length > 0) {
          let tmp = this.buffer
          this.buffer = []
          await this.handleEvent(tmp[0], tmp[1])
        }
      },
      async searchEvent (nodeMap, payload) {
        await this.handleEvent(nodeMap, payload)

        //We keep the search params in a buffer
        //so we can undo the search highlight effect
        payload.method = 'cancel'
        this.buffer = [nodeMap, payload]
      },
      async handleThis (nodeMap, payload, node) {
        if (payload.method === 'toggleSelected') {
          //If we have a single select tree, we deselect all other nodes when the current node is clicked
          if (!this.multiSelect) {
            if (node.selected && node.id !== payload.target.id) {
              node.selected = false
            }
          } else {
            return node
          }
        }

        //If the current node is not in the tested node map, we don't do any processing
        //We still have to update children in case a new node has been selected and we're in single select mode.
        if (node.id !== nodeMap[0]) {
          //We reset the node back to normal mode as a precaution.
          node.mode = 1
          if (payload.method === 'toggleSelected'&&!this.multiSelect) {
            if (node.children.length) {
              let td = []
              for (let subNode of node.children) {
                let n = await this.handleThis(nodeMap.slice(1), payload, subNode)
                if (n) {
                  td.push(n)
                }
              }
              node.children = td
            }
          }
          return node
        }

        //The node currently tested is a part of the tree for which an open/close event has been triggered
        //so if a show event is triggered we open every node in the tree
        if (payload.method === 'show') {
          node.open = true
        }

        //Modes: Default:1, Edit:2, Add:6
        if (node.id === payload.target.id) {
          switch (payload.method) {
            case 'show':
              node.mode = 5
              break
            case 'open':
              node.open = true
              break
            case 'close':
              node.open = false
              break
            case 'toggleShow':
              node.open = !node.open
              break
            case 'toggleSelected':
              if (node.children.length) {
                let td = []
                for (let subNode of node.children) {
                  let n = await this.handleThis(nodeMap.slice(1), payload, subNode)
                  if (n) {
                    td.push(n)
                  }
                }
                node.children = td
              }
              break
            case 'add':
              node.open = true
              node.children.push(
                {label: '', id: '', open: true, selected: false, mode: 6, children: [], parent: node.id})
              break
            case 'edit':
              node.mode = 2
              break
            case 'update':
              let response
              if (node.mode === 6) {
                response = await this.addCallback(node, payload.newValue)
                //In case of failure, we don't bother keeping the node
                if (response === false) {
                  return
                } else {
                  node = response
                }
              } else {
                response = await this.editCallback(node, payload.newValue)
                if (response === false) {
                  node.mode = 1
                }
              }
              node.label = payload.newValue
              node.mode = 1
              break
            case 'cancelCreation':
              //If the user creates a node a changes their mind
              return
            case 'delete':
              if (node.id.length) {
                await this.deleteCallback(node.id)
              }
              //Not returning the node will effectively remove it from the tree.
              return
            case 'cancel':
            case 'reset':
              node.mode = 1
              break
          }
          //In case we want to manipulate the children of a sibling element
        } else if (node.children.length) {
          let td = []
          for (let subNode of node.children) {
            let n = await this.handleThis(nodeMap.slice(1), payload, subNode)
            if (n) {
              td.push(n)
            }
          }
          node.children = td
        }
        return node
      }
    }
  }
</script>