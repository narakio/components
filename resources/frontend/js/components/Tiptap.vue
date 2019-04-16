<template>
  <div id="tiptap-editor" class="editor" @click="focusEditor">
    <editor-menu-bubble class="menububble" :editor="editor" @hide="hideLinkMenu">
      <div slot-scope="{ commands, isActive, getMarkAttrs, menu }"
           class="menububble"
           :class="{ 'is-active': menu.isActive }"
           :style="`left: ${menu.left+50}px; bottom: ${menu.bottom-75}px;`">
        <form v-if="linkMenuIsActive"
              class="menububble__form"
              @submit.prevent="setLinkUrl(commands.link, linkUrl)">
          <input class="menububble__input" type="text" v-model="linkUrl"
                 placeholder="https://" ref="linkInput"
                 @keydown.esc="hideLinkMenu"/>
          <button class="menububble__button" @click="setLinkUrl(commands.link, null)"
                  type="button" :title="$t('wysiwyg.delete_link')" :aria-label="$t('wysiwyg.delete_link')">
            <i class="fa fa-trash"></i>
          </button>
        </form>
        <template v-else>
          <button class="menububble__button"
                  @click="showLinkMenu(getMarkAttrs('link'))"
                  :class="{ 'is-active': isActive.link() }">
            <span>{{ isActive.link() ? $t('wysiwyg.update_link') : $t('wysiwyg.add_link')}}</span>
            <i class="fa fa-link"></i>
          </button>
        </template>
      </div>
    </editor-menu-bubble>
    <editor-menu-bar :editor="editor">
      <div class="menubar" slot-scope="{ commands, isActive }">
        <button type="button"
                class="menubar__button"
                :class="{ 'is-active': isActive.bold() }"
                @click="commands.bold">
          <i class="fa fa-bold"></i>
        </button>
        <button type="button"
                class="menubar__button"
                :class="{ 'is-active': isActive.italic() }"
                @click="commands.italic">
          <i class="fa fa-italic"></i>
        </button>
        <button type="button"
                class="menubar__button"
                :class="{ 'is-active': isActive.strike() }"
                @click="commands.strike">
          <i class="fa fa-strikethrough"></i>
        </button>
        <button type="button"
                class="menubar__button"
                :class="{ 'is-active': isActive.underline() }"
                @click="commands.underline">
          <i class="fa fa-underline"></i>
        </button>
        <button type="button"
                class="menubar__button"
                :class="{ 'is-active': isActive.bullet_list() }"
                @click="commands.bullet_list">
          <i class="fa fa-list-ul"></i>
        </button>
        <button type="button"
                class="menubar__button"
                :class="{ 'is-active': isActive.code_block() }"
                @click="commands.code_block">
          <i class="fa fa-code"></i>
        </button>
      </div>
    </editor-menu-bar>
    <div class="suggestion-list" v-show="showSuggestions" ref="suggestions">
      <template v-if="hasResults">
        <div v-for="(user, index) in filteredUsers"
             :key="user.name"
             class="suggestion-list__item"
             :class="{ 'is-selected': navigatedUserIndex === index }"
             @click="selectUser(user)">
          <figure>
            <img v-if="user.avatar" :src="user.avatar"/>
            <img v-else src="/media/img/site/placeholder_tb.png"/>
          </figure>
          <span class="label-name">{{ user.full_name }} (@{{user.name}})</span>
        </div>
      </template>
      <div v-else class="suggestion-list__item is-empty">
        {{$t('wysiwyg.no_results')}}
      </div>
    </div>
    <editor-content class="editor__content" :editor="editor"></editor-content>
  </div>
</template>
<script>
  import tippy from 'tippy.js'
  import axios from 'axios'
  import { Editor, EditorContent, EditorMenuBar, EditorMenuBubble } from 'tiptap'
  import {
    Blockquote,
    Bold,
    BulletList,
    CodeBlock,
    HardBreak,
    Italic,
    Link,
    ListItem,
    Mention,
    OrderedList,
    Strike,
    Underline
  } from 'tiptap-extensions'

  export default {
    name: 'tiptap',
    components: {
      EditorContent,
      EditorMenuBar,
      EditorMenuBubble
    },
    props: {
      editMode: {required: true},
      isRootElem: {type: Boolean},
      content: {type: String, default: () => null},
      searchUrl: {required: true, type: String}
    },
    data () {
      return {
        editor: new Editor({
          extensions: [
            new Blockquote(),
            new BulletList(),
            new CodeBlock(),
            new HardBreak(),
            new ListItem(),
            new OrderedList(),
            new Bold(),
            new Italic(),
            new Link(),
            new Strike(),
            new Underline(),
            new Mention(this.getMentionAttrs())
          ]
        }),
        linkUrl: null,
        linkMenuIsActive: false,
        query: null,
        tippyIsOn: false,
        suggestionRange: null,
        filteredUsers: [],
        navigatedUserIndex: 0,
        searchTriggerDelay: 200,
        searchTriggerLength: 1,
        timer: 0,
        lastInput: null,
        insertMention: () => {}
      }
    },
    mounted () {
      let vm = this
      this.$nextTick(() => {
        if (this.isRootElem) {
          this.$root.$emit('tiptapIsMounted')
        }
        if (this.content !== null) {
          this.editor.setContent(this.content)
        }
        this.properFocus()
      })

    },
    computed: {
      hasResults () {
        return this.filteredUsers.length
      },
      showSuggestions () {
        return this.query || this.hasResults || this.tippyIsOn
      }
    },
    watch: {
      editMode () {
        this.properFocus()
      },
      content () {
        this.editor.setContent(this.content)
      }
    },
    methods: {
      properFocus () {
        if (this.editMode !== false) {
          this.editor.focus()
        }
      },
      showLinkMenu (attrs) {
        this.linkUrl = attrs.href
        this.linkMenuIsActive = true
        this.$nextTick(() => {
          this.$refs.linkInput.focus()
        })
      },
      hideLinkMenu () {
        this.linkUrl = null
        this.linkMenuIsActive = false
      },
      setLinkUrl (command, url) {
        command({href: url})
        this.hideLinkMenu()
        this.editor.focus()
      },

      // navigate to the previous item
      // if it's the first item, navigate to the last one
      upHandler () {
        this.navigatedUserIndex = ((this.navigatedUserIndex + this.filteredUsers.length) - 1) %
          this.filteredUsers.length
      },
      // navigate to the next item
      // if it's the last item, navigate to the first one
      downHandler () {
        this.navigatedUserIndex = (this.navigatedUserIndex + 1) % this.filteredUsers.length
      },
      enterHandler () {
        const user = this.filteredUsers[this.navigatedUserIndex]

        if (user) {
          this.selectUser(user)
        }
      },
      // we have to replace our suggestion text with a mention
      // so it's important to also pass the position of your suggestion text
      selectUser (user) {
        this.insertMention({
          range: this.suggestionRange,
          attrs: {
            id: user.id,
            label: user.name
          }
        })
        this.editor.focus()
      },
      // renders a popup with suggestions
      // tiptap provides a virtualNode object for using popper.js (or tippy.js) for popups
      renderPopup (node) {
        if (this.popup) {
          return
        }
        this.popup = tippy(node, {
          content: this.$refs.suggestions,
          trigger: 'mouseenter',
          interactive: true,
          theme: 'dark',
          placement: 'bottom-start',
          inertia: true,
          duration: [400, 200],
          showOnInit: true,
          arrow: true,
          lazy: false,
          arrowType: 'round',
          ignoreAttributes: true
        })
      },

      destroyPopup () {
        if (this.popup) {
          this.popup.destroy()
          this.popup = null
        }
      },
      focusEditor (e) {
        if (e.target.className.match(/ProseMirror/)) {
          this.editor.focus()
        }
      },
      getData () {
        return this.editor.getHTML()
      },
      clearContent () {
        this.editor.clearContent()
      },
      getMentionAttrs () {
        return {
          items: () => [
            // {id:0,name:'please type at least 2 characters'}
          ],
          onEnter: ({
                      items, query, range, command, virtualNode
                    }) => {
            this.tippyIsOn = true
            this.query = query
            this.filteredUsers = items
            this.suggestionRange = range
            this.renderPopup(virtualNode)
            // we save the command for inserting a selected mention
            // this allows us to call it inside of our custom popup
            // via keyboard navigation and on click
            this.insertMention = command
          },
          // is called when a suggestion has changed
          onChange: async ({
                             items, query, range, virtualNode
                           }) => {
            this.query = query

            if (!query) {
              return items
            }
            if (query.length > this.searchTriggerLength && query !== this.lastInput) {
              this.lastInput = query
              clearTimeout(this.timer)
              let vm = this
              this.timer = setTimeout(async function () {
                let {data} = await axios.post(`${vm.searchUrl}/search/user`, {q: query})
                let results = []
                if (data !== null) {
                  data.forEach((val) => {
                    val.id = val.username
                    val.name = val.username
                    delete val.username
                    results.push(val)
                  })
                }
                vm.filteredUsers = results
                vm.suggestionRange = range
                vm.navigatedUserIndex = 0
                vm.renderPopup(virtualNode)
              }, this.searchTriggerDelay)
            }

          },
          // is called when a suggestion is cancelled
          onExit: () => {
            // reset all saved values
            this.tippyIsOn = false
            this.query = null
            this.filteredUsers = []
            this.suggestionRange = null
            this.navigatedUserIndex = 0
            this.destroyPopup()
          },
          // is called on every keyDown event while a suggestion is active
          onKeyDown: ({event}) => {
            // pressing up arrow
            if (event.keyCode === 38) {
              this.upHandler()
              return true
            }
            // pressing down arrow
            if (event.keyCode === 40) {
              this.downHandler()
              return true
            }
            // pressing enter
            if (event.keyCode === 13) {
              this.enterHandler()
              return true
            }

            return false
          }
          // onFilter: (items, query) => {
          //   return []
          // }
        }
      }
    },
    beforeDestroy () {
      this.editor.destroy()
    }
  }
</script>