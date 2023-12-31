export const defaultThemeStyles = {
	general: {
		color: '#484848',
		colorButtonClear: '#1976d2',
		colorButton: '#fff',
		backgroundColorButton: '#1976d2',
		backgroundInput: '#fff',
		colorPlaceholder: '#9ca6af',
		colorCaret: '#1976d2',
		colorSpinner: '#333',
		borderStyle: '1px solid #e1e4e8',
		backgroundScrollIcon: '#fff'
	},

	container: {
		border: 'none',
		borderRadius: '0',
		boxShadow: 'none' //'0px 1px 2px 0px rgba(0, 0, 0, 0.14), 0px 1px 5px 0px rgba(0, 0, 0, 0.12)'
	},

	header: {
		background: '#F7F7F7',
		colorRoomName: '#0a0a0a',
		colorRoomInfo: '#9ca6af'
	},

	footer: {
		background: '#F7F7F7',
		borderStyleInput: '1px solid #e1e4e8',
		borderInputSelected: '#1976d2',
		backgroundReply: '#e5e5e6',
		backgroundTagActive: '#e5e5e6',
		backgroundTag: '#f8f9fa'
	},

	content: {
		background: '#F7F7F7'
	},

	sidemenu: {
		background: '#fff',
		backgroundHover: '#f6f6f6',
		backgroundActive: '#e5effa',
		colorActive: '#1976d2',
		borderColorSearch: '#e1e5e8'
	},

	dropdown: {
		background: '#fff',
		backgroundHover: '#f6f6f6'
	},

	message: {
		background: '#FFF',
		backgroundMe: '#ccf2cf',
		color: '#0a0a0a',
		colorStarted: '#9ca6af',
		backgroundDeleted: '#dadfe2',
		backgroundSelected: '#c2dcf2',
		colorDeleted: '#757e85',
		colorUsername: '#9ca6af',
		colorTimestamp: '#828c94',
		backgroundDate: '#e5effa',
		colorDate: '#505a62',
		backgroundSystem: '#e5effa',
		colorSystem: '#505a62',
		backgroundMedia: 'rgba(0, 0, 0, 0.15)',
		backgroundReply: 'rgba(0, 0, 0, 0.08)',
		colorReplyUsername: '#0a0a0a',
		colorReply: '#6e6e6e',
		colorTag: '#0d579c',
		backgroundImage: '#ddd',
		colorNewMessages: '#1976d2',
		backgroundScrollCounter: '#0696c7',
		colorScrollCounter: '#fff',
		backgroundReaction: '#eee',
		borderStyleReaction: '1px solid #eee',
		backgroundReactionHover: '#fff',
		borderStyleReactionHover: '1px solid #ddd',
		colorReactionCounter: '#0a0a0a',
		backgroundReactionMe: '#cfecf5',
		borderStyleReactionMe: '1px solid #3b98b8',
		backgroundReactionHoverMe: '#cfecf5',
		borderStyleReactionHoverMe: '1px solid #3b98b8',
		colorReactionCounterMe: '#0b59b3',
		backgroundAudioRecord: '#eb4034',
		backgroundAudioLine: 'rgba(0, 0, 0, 0.15)',
		backgroundAudioProgress: '#455247',
		backgroundAudioProgressSelector: '#455247',
		colorFileExtension: '#757e85'
	},

	markdown: {
		background: 'rgba(239, 239, 239, 0.7)',
		border: 'rgba(212, 212, 212, 0.9)',
		color: '#e01e5a',
		colorMulti: '#0a0a0a'
	},

	room: {
		colorUsername: '#0a0a0a',
		colorMessage: '#67717a',
		colorTimestamp: '#a2aeb8',
		colorStateOnline: '#4caf50',
		colorStateOffline: '#9ca6af',
		backgroundCounterBadge: '#0696c7',
		colorCounterBadge: '#fff'
	},

	emoji: {
		background: '#fff'
	},

	icons: {
		search: '#9ca6af',
		add: '#1976d2',
		toggle: '#0a0a0a',
		menu: '#0a0a0a',
		close: '#9ca6af',
		closeImage: '#fff',
		file: '#1976d2',
		paperclip: '#1976d2',
		closeOutline: '#000',
		closePreview: '#fff',
		send: '#1976d2',
		sendDisabled: '#9ca6af',
		emoji: '#1976d2',
		emojiReaction: 'rgba(0, 0, 0, 0.3)',
		document: '#1976d2',
		pencil: '#9e9e9e',
		checkmark: '#9e9e9e',
		checkmarkSeen: '#0696c7',
		eye: '#fff',
		dropdownMessage: '#fff',
		dropdownMessageBackground: 'rgba(0, 0, 0, 0.25)',
		dropdownRoom: '#9e9e9e',
		dropdownScroll: '#0a0a0a',
		microphone: '#1976d2',
		audioPlay: '#455247',
		audioPause: '#455247',
		audioCancel: '#eb4034',
		audioConfirm: '#1ba65b'
	}
}

export const cssThemeVars = ({
	general,
	container,
	header,
	footer,
	sidemenu,
	content,
	dropdown,
	message,
	markdown,
	room,
	emoji,
	icons
}) => {
	return {
		// general
		'--chat-color': general.color,
		'--chat-color-button-clear': general.colorButtonClear,
		'--chat-color-button': general.colorButton,
		'--chat-bg-color-button': general.backgroundColorButton,
		'--chat-bg-color-input': general.backgroundInput,
		'--chat-color-spinner': general.colorSpinner,
		'--chat-color-placeholder': general.colorPlaceholder,
		'--chat-color-caret': general.colorCaret,
		'--chat-border-style': general.borderStyle,
		'--chat-bg-scroll-icon': general.backgroundScrollIcon,

		// container
		'--chat-container-border': container.border,
		'--chat-container-border-radius': container.borderRadius,
		'--chat-container-box-shadow': container.boxShadow,

		// header
		'--chat-header-bg-color': header.background,
		'--chat-header-color-name': header.colorRoomName,
		'--chat-header-color-info': header.colorRoomInfo,

		// footer
		'--chat-footer-bg-color': footer.background,
		'--chat-border-style-input': footer.borderStyleInput,
		'--chat-border-color-input-selected': footer.borderInputSelected,
		'--chat-footer-bg-color-reply': footer.backgroundReply,
		'--chat-footer-bg-color-tag-active': footer.backgroundTagActive,
		'--chat-footer-bg-color-tag': footer.backgroundTag,

		// content
		'--chat-content-bg-color': content.background,

		// sidemenu
		'--chat-sidemenu-bg-color': sidemenu.background,
		'--chat-sidemenu-bg-color-hover': sidemenu.backgroundHover,
		'--chat-sidemenu-bg-color-active': sidemenu.backgroundActive,
		'--chat-sidemenu-color-active': sidemenu.colorActive,
		'--chat-sidemenu-border-color-search': sidemenu.borderColorSearch,

		// dropdown
		'--chat-dropdown-bg-color': dropdown.background,
		'--chat-dropdown-bg-color-hover': dropdown.backgroundHover,

		// message
		'--chat-message-bg-color': message.background,
		'--chat-message-bg-color-me': message.backgroundMe,
		'--chat-message-color-started': message.colorStarted,
		'--chat-message-bg-color-deleted': message.backgroundDeleted,
		'--chat-message-bg-color-selected': message.backgroundSelected,
		'--chat-message-color-deleted': message.colorDeleted,
		'--chat-message-color-username': message.colorUsername,
		'--chat-message-color-timestamp': message.colorTimestamp,
		'--chat-message-bg-color-date': message.backgroundDate,
		'--chat-message-color-date': message.colorDate,
		'--chat-message-bg-color-system': message.backgroundSystem,
		'--chat-message-color-system': message.colorSystem,
		'--chat-message-color': message.color,
		'--chat-message-bg-color-media': message.backgroundMedia,
		'--chat-message-bg-color-reply': message.backgroundReply,
		'--chat-message-color-reply-username': message.colorReplyUsername,
		'--chat-message-color-reply-content': message.colorReply,
		'--chat-message-color-tag': message.colorTag,
		'--chat-message-bg-color-image': message.backgroundImage,
		'--chat-message-color-new-messages': message.colorNewMessages,
		'--chat-message-bg-color-scroll-counter': message.backgroundScrollCounter,
		'--chat-message-color-scroll-counter': message.colorScrollCounter,
		'--chat-message-bg-color-reaction': message.backgroundReaction,
		'--chat-message-border-style-reaction': message.borderStyleReaction,
		'--chat-message-bg-color-reaction-hover': message.backgroundReactionHover,
		'--chat-message-border-style-reaction-hover':
			message.borderStyleReactionHover,
		'--chat-message-color-reaction-counter': message.colorReactionCounter,
		'--chat-message-bg-color-reaction-me': message.backgroundReactionMe,
		'--chat-message-border-style-reaction-me': message.borderStyleReactionMe,
		'--chat-message-bg-color-reaction-hover-me':
			message.backgroundReactionHoverMe,
		'--chat-message-border-style-reaction-hover-me':
			message.borderStyleReactionHoverMe,
		'--chat-message-color-reaction-counter-me': message.colorReactionCounterMe,
		'--chat-message-bg-color-audio-record': message.backgroundAudioRecord,
		'--chat-message-bg-color-audio-line': message.backgroundAudioLine,
		'--chat-message-bg-color-audio-progress': message.backgroundAudioProgress,
		'--chat-message-bg-color-audio-progress-selector':
			message.backgroundAudioProgressSelector,
		'--chat-message-color-file-extension': message.colorFileExtension,

		// markdown
		'--chat-markdown-bg': markdown.background,
		'--chat-markdown-border': markdown.border,
		'--chat-markdown-color': markdown.color,
		'--chat-markdown-color-multi': markdown.colorMulti,

		// room
		'--chat-room-color-username': room.colorUsername,
		'--chat-room-color-message': room.colorMessage,
		'--chat-room-color-timestamp': room.colorTimestamp,
		'--chat-room-color-online': room.colorStateOnline,
		'--chat-room-color-offline': room.colorStateOffline,
		'--chat-room-bg-color-badge': room.backgroundCounterBadge,
		'--chat-room-color-badge': room.colorCounterBadge,

		// emoji
		'--chat-emoji-bg-color': emoji.background,

		// icons
		'--chat-icon-color-search': icons.search,
		'--chat-icon-color-add': icons.add,
		'--chat-icon-color-toggle': icons.toggle,
		'--chat-icon-color-menu': icons.menu,
		'--chat-icon-color-close': icons.close,
		'--chat-icon-color-close-image': icons.closeImage,
		'--chat-icon-color-file': icons.file,
		'--chat-icon-color-paperclip': icons.paperclip,
		'--chat-icon-color-close-outline': icons.closeOutline,
		'--chat-icon-color-close-preview': icons.closePreview,
		'--chat-icon-color-send': icons.send,
		'--chat-icon-color-send-disabled': icons.sendDisabled,
		'--chat-icon-color-emoji': icons.emoji,
		'--chat-icon-color-emoji-reaction': icons.emojiReaction,
		'--chat-icon-color-document': icons.document,
		'--chat-icon-color-pencil': icons.pencil,
		'--chat-icon-color-checkmark': icons.checkmark,
		'--chat-icon-color-checkmark-seen': icons.checkmarkSeen,
		'--chat-icon-color-eye': icons.eye,
		'--chat-icon-color-dropdown-message': icons.dropdownMessage,
		'--chat-icon-bg-dropdown-message': icons.dropdownMessageBackground,
		'--chat-icon-color-dropdown-room': icons.dropdownRoom,
		'--chat-icon-color-dropdown-scroll': icons.dropdownScroll,
		'--chat-icon-color-microphone': icons.microphone,
		'--chat-icon-color-audio-play': icons.audioPlay,
		'--chat-icon-color-audio-pause': icons.audioPause,
		'--chat-icon-color-audio-cancel': icons.audioCancel,
		'--chat-icon-color-audio-confirm': icons.audioConfirm
	}
}
