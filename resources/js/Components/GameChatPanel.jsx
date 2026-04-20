import { useEffect, useMemo, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';

function appendUniqueMessage(currentMessages, incomingMessage) {
    const index = currentMessages.findIndex((message) => message.id === incomingMessage.id);

    if (index === -1) {
        return [...currentMessages, incomingMessage];
    }

    const nextMessages = [...currentMessages];
    nextMessages[index] = incomingMessage;

    return nextMessages;
}

export default function GameChatPanel({ chat, gameTitle }) {
    const { auth } = usePage().props;
    const currentUser = auth?.user;
    const [messages, setMessages] = useState(chat.messages ?? []);
    const [draft, setDraft] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);
    const scrollerRef = useRef(null);

    useEffect(() => {
        setMessages(chat.messages ?? []);
    }, [chat.messages]);

    useEffect(() => {
        if (! window.Echo || ! chat.channel) {
            return undefined;
        }

        const channel = window.Echo.private(chat.channel);
        const handler = (event) => {
            if (! event?.message) {
                return;
            }

            setMessages((currentMessages) => appendUniqueMessage(currentMessages, event.message));
        };

        channel.listen('.chat.message.sent', handler);

        return () => {
            channel.stopListening('.chat.message.sent', handler);
            window.Echo.leave(chat.channel);
        };
    }, [chat.channel]);

    useEffect(() => {
        const scroller = scrollerRef.current;

        if (! scroller) {
            return;
        }

        scroller.scrollTop = scroller.scrollHeight;
    }, [messages]);

    const emptyState = useMemo(
        () => ({
            title: 'Sin mensajes todavía',
            copy: `Abre la conversación de ${gameTitle} y escribe el primero.`,
        }),
        [gameTitle]
    );

    async function submitMessage(event) {
        event.preventDefault();

        const cleanDraft = draft.trim();

        if (! cleanDraft || sending) {
            return;
        }

        setSending(true);
        setError('');

        try {
            const response = await window.axios.post(chat.post_url, {
                message: cleanDraft,
            });

            setMessages((currentMessages) => appendUniqueMessage(currentMessages, response.data.message));
            setDraft('');
        } catch (requestError) {
            setError(requestError.response?.data?.message ?? 'No se ha podido enviar el mensaje.');
        } finally {
            setSending(false);
        }
    }

    return (
        <aside className="card chat-card stack">
            <div className="frame-header">
                <div className="stack compact-gap">
                    <span className="eyebrow">Chat en vivo</span>
                    <h2>Partida abierta</h2>
                </div>
                <span className="pill success">websocket</span>
            </div>

            <div ref={scrollerRef} className="chat-feed">
                {messages.length === 0 ? (
                    <div className="chat-empty">
                        <strong>{emptyState.title}</strong>
                        <span>{emptyState.copy}</span>
                    </div>
                ) : (
                    messages.map((message) => {
                        const ownMessage = message.user?.id === currentUser?.id;

                        return (
                            <article
                                key={message.id}
                                className={`chat-message ${ownMessage ? 'own' : ''}`}
                            >
                                <header className="chat-message-head">
                                    <strong>{ownMessage ? 'Tú' : message.user?.name}</strong>
                                    <span>
                                        {message.user?.role_label}
                                        {message.created_at_label ? ` · ${message.created_at_label}` : ''}
                                    </span>
                                </header>
                                <p>{message.message}</p>
                            </article>
                        );
                    })
                )}
            </div>

            <form className="chat-form" onSubmit={submitMessage}>
                <label className="sr-only" htmlFor="chat-message">
                    Escribe un mensaje
                </label>
                <textarea
                    id="chat-message"
                    className="textarea chat-input"
                    rows="3"
                    placeholder="Escribe y pulsa Enter"
                    value={draft}
                    onChange={(event) => setDraft(event.target.value)}
                    onKeyDown={(event) => {
                        if (event.key === 'Enter' && ! event.shiftKey) {
                            event.preventDefault();
                            void submitMessage(event);
                        }
                    }}
                />

                <div className="chat-form-footer">
                    <span className="muted">Shift + Enter para salto de línea</span>
                    <button className="button" type="submit" disabled={sending}>
                        {sending ? 'Enviando...' : 'Enviar'}
                    </button>
                </div>

                {error && <div className="error">{error}</div>}
            </form>
        </aside>
    );
}
