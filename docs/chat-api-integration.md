# Integración de Chat en Tiempo Real para OiDiVi Helper

Este documento proporciona las especificaciones técnicas para integrar la funcionalidad de chat en tiempo real en el frontend de OiDiVi Helper.

## 1. Configuración de WebSockets

### 1.1 Instalación de Dependencias

```bash
npm install socket.io-client
```

### 1.2 Configuración del Cliente Socket.io

```javascript
// utils/socket.js
import { io } from 'socket.io-client';

// Obtener la URL del servidor de WebSockets desde las variables de entorno
const SOCKET_URL = process.env.NEXT_PUBLIC_REVERB_HOST || 'http://localhost:8080';
const SOCKET_KEY = process.env.NEXT_PUBLIC_REVERB_APP_KEY;

let socket;

export const initializeSocket = (token) => {
  if (!socket) {
    socket = io(SOCKET_URL, {
      auth: {
        token: token
      },
      query: {
        key: SOCKET_KEY
      }
    });

    // Eventos de conexión
    socket.on('connect', () => {
      console.log('Socket connected');
    });

    socket.on('connect_error', (error) => {
      console.error('Socket connection error:', error);
    });

    socket.on('disconnect', () => {
      console.log('Socket disconnected');
    });
  }
  
  return socket;
};

export const getSocket = () => {
  if (!socket) {
    throw new Error('Socket not initialized');
  }
  
  return socket;
};

export const disconnectSocket = () => {
  if (socket) {
    socket.disconnect();
    socket = null;
  }
};
```

## 2. Endpoints de la API

### 2.1 Chats

#### Obtener lista de chats

```
GET /api/v1/chats
```

Respuesta:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_one": 1,
        "user_two": 2,
        "service_request_id": null,
        "is_group": false,
        "name": null,
        "description": null,
        "last_message_at": "2023-06-15T12:00:00.000000Z",
        "created_at": "2023-06-15T10:00:00.000000Z",
        "updated_at": "2023-06-15T12:00:00.000000Z",
        "user_one": {
          "id": 1,
          "name": "Usuario 1",
          "profile_photo_url": "https://example.com/photos/1.jpg"
        },
        "user_two": {
          "id": 2,
          "name": "Usuario 2",
          "profile_photo_url": "https://example.com/photos/2.jpg"
        },
        "messages_count": 5
      }
    ],
    "first_page_url": "http://api.example.com/api/v1/chats?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://api.example.com/api/v1/chats?page=1",
    "next_page_url": null,
    "path": "http://api.example.com/api/v1/chats",
    "per_page": 20,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

#### Crear un nuevo chat

```
POST /api/v1/chats
```

Parámetros:
```json
{
  "user_id": 2,
  "service_request_id": null,
  "initial_message": "Hola, ¿cómo estás?"
}
```

Respuesta:
```json
{
  "status": "success",
  "message": "Chat created successfully",
  "data": {
    "id": 1,
    "user_one": 1,
    "user_two": 2,
    "service_request_id": null,
    "is_group": false,
    "name": null,
    "description": null,
    "last_message_at": "2023-06-15T12:00:00.000000Z",
    "created_at": "2023-06-15T10:00:00.000000Z",
    "updated_at": "2023-06-15T12:00:00.000000Z",
    "user_one": {
      "id": 1,
      "name": "Usuario 1",
      "profile_photo_url": "https://example.com/photos/1.jpg"
    },
    "user_two": {
      "id": 2,
      "name": "Usuario 2",
      "profile_photo_url": "https://example.com/photos/2.jpg"
    }
  }
}
```

#### Obtener detalles de un chat

```
GET /api/v1/chats/{chatId}
```

Respuesta:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "user_one": 1,
    "user_two": 2,
    "service_request_id": null,
    "is_group": false,
    "name": null,
    "description": null,
    "last_message_at": "2023-06-15T12:00:00.000000Z",
    "created_at": "2023-06-15T10:00:00.000000Z",
    "updated_at": "2023-06-15T12:00:00.000000Z",
    "user_one": {
      "id": 1,
      "name": "Usuario 1",
      "profile_photo_url": "https://example.com/photos/1.jpg"
    },
    "user_two": {
      "id": 2,
      "name": "Usuario 2",
      "profile_photo_url": "https://example.com/photos/2.jpg"
    },
    "service_request": null
  }
}
```

#### Marcar un chat como leído

```
POST /api/v1/chats/{chatId}/read
```

Respuesta:
```json
{
  "status": "success",
  "message": "Chat marked as read"
}
```

#### Enviar estado de escritura

```
POST /api/v1/chats/{chatId}/typing
```

Parámetros:
```json
{
  "is_typing": true
}
```

Respuesta:
```json
{
  "status": "success",
  "message": "Typing status sent"
}
```

### 2.2 Mensajes

#### Obtener mensajes de un chat

```
GET /api/v1/chats/{chatId}/messages
```

Respuesta:
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "chat_id": 1,
        "sender_id": 1,
        "receiver_id": 2,
        "message": "Hola, ¿cómo estás?",
        "type": "text",
        "media_url": null,
        "media_type": null,
        "metadata": null,
        "seen": true,
        "created_at": "2023-06-15T10:00:00.000000Z",
        "updated_at": "2023-06-15T10:00:00.000000Z",
        "sender": {
          "id": 1,
          "name": "Usuario 1",
          "profile_photo_url": "https://example.com/photos/1.jpg"
        }
      }
    ],
    "first_page_url": "http://api.example.com/api/v1/chats/1/messages?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://api.example.com/api/v1/chats/1/messages?page=1",
    "next_page_url": null,
    "path": "http://api.example.com/api/v1/chats/1/messages",
    "per_page": 50,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

#### Enviar un mensaje

```
POST /api/v1/chats/{chatId}/messages
```

Parámetros (mensaje de texto):
```json
{
  "message": "Hola, ¿cómo estás?",
  "type": "text"
}
```

Parámetros (mensaje con archivo):
```
POST /api/v1/chats/{chatId}/messages
Content-Type: multipart/form-data

message: Hola, aquí tienes el archivo
type: file
media: [archivo]
```

Respuesta:
```json
{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "id": 1,
    "chat_id": 1,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "Hola, ¿cómo estás?",
    "type": "text",
    "media_url": null,
    "media_type": null,
    "metadata": null,
    "seen": false,
    "created_at": "2023-06-15T10:00:00.000000Z",
    "updated_at": "2023-06-15T10:00:00.000000Z",
    "sender": {
      "id": 1,
      "name": "Usuario 1",
      "profile_photo_url": "https://example.com/photos/1.jpg"
    }
  }
}
```

#### Marcar un mensaje como leído

```
POST /api/v1/chats/{chatId}/messages/{messageId}/seen
```

Respuesta:
```json
{
  "status": "success",
  "message": "Message marked as seen"
}
```

## 3. Eventos de WebSocket

### 3.1 Suscripción a Canales

Para suscribirse a un canal de chat:

```javascript
const socket = getSocket();
const chatId = 1;

// Suscribirse al canal privado del chat
socket.emit('subscribe', `private-chat.${chatId}`);
```

### 3.2 Eventos de Mensajes

#### Mensaje Enviado

```javascript
socket.on(`private-chat.${chatId}`, (data) => {
  if (data.event === 'message.sent') {
    const message = data.data;
    // Actualizar la UI con el nuevo mensaje
    console.log('Nuevo mensaje recibido:', message);
  }
});
```

#### Mensaje Visto

```javascript
socket.on(`private-chat.${chatId}`, (data) => {
  if (data.event === 'message.seen') {
    const { message_id, user_id, seen_at } = data.data;
    // Actualizar el estado de lectura del mensaje en la UI
    console.log('Mensaje visto:', message_id, 'por usuario:', user_id);
  }
});
```

#### Usuario Escribiendo

```javascript
socket.on(`private-chat.${chatId}`, (data) => {
  if (data.event === 'user.typing') {
    const { user_id, user_name, is_typing, timestamp } = data.data;
    // Mostrar/ocultar indicador de escritura en la UI
    console.log('Usuario escribiendo:', user_name, is_typing);
  }
});
```

### 3.3 Envío de Eventos

#### Enviar Estado de Escritura

```javascript
const sendTypingStatus = (chatId, isTyping) => {
  const socket = getSocket();
  socket.emit('client-typing', {
    chat_id: chatId,
    is_typing: isTyping
  });
};
```

## 4. Ejemplo de Implementación en React

### 4.1 Componente de Lista de Chats

```jsx
import { useState, useEffect } from 'react';
import { getSocket } from '../utils/socket';
import axios from 'axios';

const ChatList = () => {
  const [chats, setChats] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const fetchChats = async () => {
      try {
        const response = await axios.get('/api/v1/chats');
        setChats(response.data.data.data);
        setLoading(false);
      } catch (error) {
        console.error('Error fetching chats:', error);
        setLoading(false);
      }
    };
    
    fetchChats();
  }, []);
  
  if (loading) return <div>Cargando chats...</div>;
  
  return (
    <div className="chat-list">
      {chats.map(chat => (
        <div key={chat.id} className="chat-item">
          <img 
            src={chat.user_one.profile_photo_url || '/default-avatar.png'} 
            alt={chat.user_one.name} 
            className="avatar"
          />
          <div className="chat-info">
            <h3>{chat.user_one.name}</h3>
            <p>Último mensaje: {new Date(chat.last_message_at).toLocaleString()}</p>
          </div>
          {chat.messages_count > 0 && (
            <div className="unread-badge">{chat.messages_count}</div>
          )}
        </div>
      ))}
    </div>
  );
};

export default ChatList;
```

### 4.2 Componente de Chat

```jsx
import { useState, useEffect, useRef } from 'react';
import { getSocket } from '../utils/socket';
import axios from 'axios';

const ChatWindow = ({ chatId }) => {
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');
  const [loading, setLoading] = useState(true);
  const [typing, setTyping] = useState(false);
  const [otherUserTyping, setOtherUserTyping] = useState(false);
  const messagesEndRef = useRef(null);
  const typingTimeoutRef = useRef(null);
  
  // Cargar mensajes
  useEffect(() => {
    const fetchMessages = async () => {
      try {
        const response = await axios.get(`/api/v1/chats/${chatId}/messages`);
        setMessages(response.data.data.data.reverse());
        setLoading(false);
        
        // Marcar chat como leído
        await axios.post(`/api/v1/chats/${chatId}/read`);
      } catch (error) {
        console.error('Error fetching messages:', error);
        setLoading(false);
      }
    };
    
    fetchMessages();
    
    // Suscribirse al canal de chat
    const socket = getSocket();
    socket.emit('subscribe', `private-chat.${chatId}`);
    
    // Escuchar eventos
    socket.on(`private-chat.${chatId}`, handleSocketEvent);
    
    return () => {
      socket.off(`private-chat.${chatId}`, handleSocketEvent);
    };
  }, [chatId]);
  
  // Scroll al último mensaje
  useEffect(() => {
    scrollToBottom();
  }, [messages]);
  
  const handleSocketEvent = (data) => {
    if (data.event === 'message.sent') {
      setMessages(prev => [...prev, data.data]);
    } else if (data.event === 'message.seen') {
      // Actualizar estado de lectura
      setMessages(prev => 
        prev.map(msg => 
          msg.id === data.data.message_id ? { ...msg, seen: true } : msg
        )
      );
    } else if (data.event === 'user.typing') {
      setOtherUserTyping(data.data.is_typing);
    }
  };
  
  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };
  
  const handleTyping = () => {
    if (!typing) {
      setTyping(true);
      const socket = getSocket();
      socket.emit('client-typing', {
        chat_id: chatId,
        is_typing: true
      });
      
      // Enviar estado de escritura al servidor
      axios.post(`/api/v1/chats/${chatId}/typing`, { is_typing: true });
    }
    
    // Limpiar timeout anterior
    if (typingTimeoutRef.current) {
      clearTimeout(typingTimeoutRef.current);
    }
    
    // Establecer nuevo timeout
    typingTimeoutRef.current = setTimeout(() => {
      setTyping(false);
      const socket = getSocket();
      socket.emit('client-typing', {
        chat_id: chatId,
        is_typing: false
      });
      
      // Enviar estado de escritura al servidor
      axios.post(`/api/v1/chats/${chatId}/typing`, { is_typing: false });
    }, 1000);
  };
  
  const sendMessage = async (e) => {
    e.preventDefault();
    
    if (!newMessage.trim()) return;
    
    try {
      const response = await axios.post(`/api/v1/chats/${chatId}/messages`, {
        message: newMessage,
        type: 'text'
      });
      
      setMessages(prev => [...prev, response.data.data]);
      setNewMessage('');
      
      // Marcar como no escribiendo
      if (typing) {
        setTyping(false);
        const socket = getSocket();
        socket.emit('client-typing', {
          chat_id: chatId,
          is_typing: false
        });
        
        // Enviar estado de escritura al servidor
        axios.post(`/api/v1/chats/${chatId}/typing`, { is_typing: false });
      }
    } catch (error) {
      console.error('Error sending message:', error);
    }
  };
  
  if (loading) return <div>Cargando mensajes...</div>;
  
  return (
    <div className="chat-window">
      <div className="messages-container">
        {messages.map(message => (
          <div 
            key={message.id} 
            className={`message ${message.sender_id === currentUserId ? 'sent' : 'received'}`}
          >
            <div className="message-content">
              {message.message}
              {message.seen && <span className="seen-indicator">✓✓</span>}
              {!message.seen && <span className="sent-indicator">✓</span>}
            </div>
            <div className="message-time">
              {new Date(message.created_at).toLocaleTimeString()}
            </div>
          </div>
        ))}
        <div ref={messagesEndRef} />
      </div>
      
      {otherUserTyping && (
        <div className="typing-indicator">
          El otro usuario está escribiendo...
        </div>
      )}
      
      <form onSubmit={sendMessage} className="message-form">
        <input
          type="text"
          value={newMessage}
          onChange={(e) => setNewMessage(e.target.value)}
          onKeyDown={handleTyping}
          placeholder="Escribe un mensaje..."
          className="message-input"
        />
        <button type="submit" className="send-button">
          Enviar
        </button>
      </form>
    </div>
  );
};

export default ChatWindow;
```

## 5. Consideraciones de Seguridad

1. **Autenticación**: Todas las solicitudes a la API deben incluir un token de autenticación en el encabezado `Authorization`.
2. **Canales Privados**: Los canales de chat son privados y requieren autenticación para suscribirse.
3. **Validación de Participantes**: Solo los participantes de un chat pueden acceder a sus mensajes.
4. **Rate Limiting**: Se aplican límites de tasa para evitar abusos.

## 6. Mejores Prácticas

1. **Manejo de Errores**: Implementar un manejo adecuado de errores en el frontend.
2. **Indicadores de Estado**: Mostrar indicadores de carga, escritura y estado de lectura.
3. **Paginación**: Implementar paginación para cargar mensajes antiguos.
4. **Optimización de Rendimiento**: Utilizar técnicas como virtualización para chats con muchos mensajes.
5. **Persistencia de Estado**: Considerar el uso de Redux o Context API para mantener el estado de los chats.
6. **Notificaciones**: Implementar notificaciones push para mensajes cuando la aplicación está en segundo plano. 