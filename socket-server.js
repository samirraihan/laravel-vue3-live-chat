import { createServer } from 'http';
import { Server } from 'socket.io';

const httpServer = createServer();
const io = new Server(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

const connectedUsers = new Map(); // socket.id => { id, name }

io.on('connection', (socket) => {
  console.log('User connected:', socket.id);

  socket.on('join', (userData) => {
    connectedUsers.set(socket.id, {
      id: userData.id,
      name: userData.name || 'Anonymous',
      socketId: socket.id,
    });

    socket.emit('online-users', Array.from(connectedUsers.values()));

    socket.broadcast.emit('user-joined', {
      id: userData.id,
      name: userData.name || 'Anonymous'
    });

    console.log(`User ${userData.name} joined`);
  });

  socket.on('join-room', (roomId) => {
    socket.join(roomId);
    console.log(`User ${socket.id} joined room ${roomId}`);
  });

  socket.on('leave-room', (roomId) => {
    socket.leave(roomId);
    console.log(`User ${socket.id} left room ${roomId}`);
  });

  socket.on('send-message', (messageData) => {
    const user = connectedUsers.get(socket.id);
    if (user) {
      const message = {
        id: Date.now(),
        text: messageData.text,
        user: user.name,
        userId: user.id,
        timestamp: new Date().toISOString(),
        chat_room_id: messageData.chat_room_id
      };

      io.to(messageData.chat_room_id).emit('new-message', message);
      console.log(`Message from ${user.name} in room ${messageData.chat_room_id}: ${message.text}`);
    }
  });

  socket.on('typing', (data) => {
    const user = connectedUsers.get(socket.id);
    if (user) {
      socket.broadcast.emit('user-typing', {
        userId: user.id,
        userName: user.name,
        isTyping: data.isTyping
      });
    }
  });

  socket.on('disconnect', () => {
    const user = connectedUsers.get(socket.id);
    if (user) {
      connectedUsers.delete(socket.id);
      socket.broadcast.emit('user-left', { id: user.id, name: user.name });
      console.log(`User ${user.name} disconnected`);
    }
  });
});

const PORT = process.env.SOCKET_PORT || 3000;
httpServer.listen(PORT, '0.0.0.0', () => {
  console.log(`Socket.IO server running on port ${PORT}`);
});
