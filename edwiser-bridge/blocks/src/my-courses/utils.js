export const decodeHTMLEntities = (text) => {
  const textArea = document.createElement('textarea');

  textArea.innerHTML = text;
  return textArea.value;
};
