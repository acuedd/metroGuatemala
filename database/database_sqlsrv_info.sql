INSERT [menu_categoria] ([id], [nombre], [imagen]) VALUES (1, N'Usuarios', N'fa-users');
INSERT [menu_categoria] ([id], [nombre], [imagen]) VALUES (2, N'Configuración', N'fa-cog');

INSERT [menu] ([menu_id], [page], [nombre], [modulo], [image], [categoria_id], [father]) VALUES (1, N'webservices', N'Administración de webservices', N'settings', N'', 2, NULL);